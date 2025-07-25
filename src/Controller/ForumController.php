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
            ['dateMessage' => 'ASC']
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
        $title = trim(filter_var($request->request->get('title'), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $message = trim(filter_var($request->request->get('message'), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $categoryId = $request->request->get('category');
        $category = $categoryRepository->find($categoryId);
        $user = $security->getUser();

        

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

}
