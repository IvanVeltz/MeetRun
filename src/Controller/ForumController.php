<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use DateTimeImmutable;
use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(TopicRepository $topicRepository, CategoryRepository $categoryRepository): Response
    {
        $topics = $topicRepository->findByLastPost();
        
        $categories = $categoryRepository->findAll();

        return $this->render('forum/index.html.twig', [
           "topics" => $topics,
           'categories' => $categories
        ]);
    }

    #[Route('/forum/topic/{id}', name: 'app_topic')]
    public function topicDetail(int $id, TopicRepository $topicRepository, PostRepository $postRepository): Response
    {
    
        $topic = $topicRepository->find($id);

        if (!$topic) {
            $this->addFlash('warning', 'Sujet non trouvé');
            return $this->redirectToRoute('app_forum');
        }

        $posts = $postRepository->findBy(
            ['topic' => $topic],
            ['createdAt' => 'ASC']
        );


        return $this->render('forum/topic.html.twig', [
            'topic' => $topic,
            'posts' => $posts,
        ]);
    }

    #[Route('/forum/creatTopic', name:'app_topic_create')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function topicCreate(EntityManagerInterface $em, Request $request, Security $security, CategoryRepository $categoryRepository): Response
    {
        $title = trim(filter_var($request->request->get('title'), FILTER_SANITIZE_SPECIAL_CHARS));
        $message = trim(filter_var($request->request->get('message'), FILTER_SANITIZE_SPECIAL_CHARS));
        $categoryId = $request->request->get('category');
        $category = $categoryRepository->find($categoryId);
        $user = $security->getUser();

        $token = $request->request->get('_token');
        
        if (!$this->isCsrfTokenValid('createTopic', $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        }

        

        if (!$title || !$category || !$message){
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('app_forum'); // ou la page d'origine
        }

        // Création du topic
        $topic = new Topic();
        $topic->setTitle($title);
        $topic->setCategory($category);
        $topic->setUser($user);
        $date = new \DateTimeImmutable();
        $topic->setDateCreation(\DateTime::createFromImmutable($date));

        // Création du premier post
        $post = new Post();
        $post->setMessage($message);
        $post->setDateMessage(\DateTime::createFromImmutable($date));
        $post->setUser($user);
        $post->setTopic($topic);

        $em->persist($topic);
        $em->persist($post);
        $em->flush();

        $this->addFlash('success', 'Sujet créé avec succès.');

        return $this->redirectToRoute('app_topic', ['id' => $topic->getId()]);
    }

    #[Route('/forum/topicClosed/{id}', name:'app_toggleTopicState')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function topicClosed(EntityManagerInterface $em, Topic $topic, Request $request): Response
    {
        $currentUser = $this->GetUser();

        if (!$this->isCsrfTokenValid('toogleTopicState'.$topic->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        if ($topic->getUser() !== $currentUser && !in_array('ROLE_ADMIN',$currentUser->getRoles())){
            $this->addFlash('error', 'Vous n\'avez pas les droits pour cloturer le sujet');
            return $this->redirectToRoute('app_forum');
        }

        $topic->setIsClosed(!$topic->isClosed());
        $em->flush();

        $this->addFlash('success', $topic->isClosed() ? 'Sujet clôturé.' : 'Sujet réouvert.');

        return $this->redirectToRoute('app_topic', ['id' => $topic->getId()]);
    }

    #[Route('forum/post/{id}/addMessage', name:'app_addMessage')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addMessage(EntityManagerInterface $em, Topic $topic, Request $request): Response
    {
        $message = trim(filter_var(
            $request->request->get('addMessage'),
            FILTER_SANITIZE_FULL_SPECIAL_CHARS
        ));
        $user = $this->getUser();

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('createPost', $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        }

        // On vérifie que l'utilisateur a un compte verifié
        if (!$user->isVerified()){
            $this->addFlash('warning', 'Vouse devez avoir un compte verifié pour participer au forum');
            return $this->redirectToRoute('app_user');
        }

        $post = new Post();
        $post->setMessage($message);
        $date = new \DateTimeImmutable();
        $post->setDateMessage(\DateTime::createFromImmutable($date));
        $post->setUser($user);
        $post->setTopic($topic);

        $em->persist($post);
        $em->flush();

        $this->addFlash('success', 'Message posté');

        return $this->redirectToRoute('app_topic', ['id' => $topic->getId()]);
    }

    #[Route('forum/{id}/delet-post', name:'app_delete_post', methods:'post')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deletePost(Request $request, EntityManagerInterface $em, Post $post): Response
    {
        // On récupère le token CSRF envoyé avec la requête
        $token = $request->request->get('_token');
        
        // On vérifie la validité du token CSRF
        if (!$this->isCsrfTokenValid('deletePost'.$post->getId(), $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        }

        // On vérifie que le post n'est pas déjà supprimé
        if ($post->isDeleted()){
            $this->addFlash('error', 'Ce message a déjà été supprimé');
            return $this->redirectToRoute('app_topic', ['id' => $post->getTopic()->getId()]);
        } 

        // On vérifie que l'utilisateur est l'auteur du message ou un admin
        if ($post->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce message');
            return $this->redirectToRoute('app_topic', ['id' => $post->getTopic()->getId()]);
        }

        // On anonymise le message au lieu de le supprimer
        $post->setMessage("Ce message a été supprimé");
        $post->setDeleted(true);
        $em->flush();

        $this->addFlash('success', 'Message supprimé avec succés');
        return $this->redirectToRoute('app_topic', ['id' => $post->getTopic()->getId()]);
    }

}
