<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(EventRepository $eventRepository, UserRepository $userRepository): Response
    {
        $events = $eventRepository->findUpcomingEvents(4); // Trouve les 4 prochains événements
        $users = $userRepository->findBy(['deleted' => false], ['dateOfRegister' => 'DESC'], 5);
        return $this->render('home/index.html.twig', [
            'results' => $events,
            'users' => $users
        ]);
    }
}
