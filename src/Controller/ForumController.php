<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(TopicRepository $topicRepository): Response
    {
        $topics = $topicRepository->findByLastPost();


        return $this->render('forum/index.html.twig', [
           "topics" => $topics,
        ]);
    }

    #[Route('/forum/topic/{id}', name: 'app_topic')]
    public function topicDetail(int $id, TopicRepository $topicRepository, PostRepository $postRepository): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', 'Veuillez vous connecter pour continuer.');
            return $this->redirectToRoute('app_forum');
        }

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

}
