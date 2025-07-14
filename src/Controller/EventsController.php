<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchRunForm;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(EventRepository $eventRepository, Request $request): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);

        $eventForm = $this->createForm(SearchRunForm::class, $data);
        $events = $eventRepository->findSearch($data);


        return $this->render('events/index.html.twig', [
            'eventForm' => $eventForm,
            'events' => $events,
        ]);
    }
}
