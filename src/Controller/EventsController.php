<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Photo;
use App\Data\SearchData;
use App\Form\NewEventForm;
use App\Form\SearchRunForm;
use App\Service\ImageUploader;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(
        EventRepository $eventRepository, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        ImageUploader $imageUploader): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);

        $eventForm = $this->createForm(SearchRunForm::class, $data);
        $qb = $eventRepository->getSearchNextEvents($data);
        $events = $eventRepository->findSearch($data, $qb);

        $event = new Event();
        $user = $this->getUser();
        $newEventForm = $this->createForm(NewEventForm::class, $event);
        $newEventForm->handleRequest($request);
        $event->setOrganizer($user);

        if($newEventForm->isSubmitted() && $newEventForm->isValid()){
            // Uploader les fichiers
            $photos = $newEventForm->get('photos')->getData(); 
            $filenames = $imageUploader->upload($photos, null, 'event');

            foreach ($filenames as $filename) {
                $eventImage = new Photo(); 
                $eventImage->setUrl('upload/' . $filename);
                $eventImage->setEvent($event);
                $entityManager->persist($eventImage);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_events');
        }


        return $this->render('events/index.html.twig', [
            'eventForm' => $eventForm,
            'newEventForm' => $newEventForm,
            'events' => $events,
        ]);
    }

    #[Route('/events/lastEvents', name: 'app_lastEvents')]
    public function lastEvents(
        Request $request,
        EventRepository $eventRepository
    )
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);

        $qb = $eventRepository->getSearchLastEvents($data);
        $events = $eventRepository->findSearch($data, $qb);

        return $this->render('events/lastEvents.html.twig', [
            'events' => $events
        ]);
    }

    #[Route('/event/detailsEvent/{id}', name: 'app_detailEvent')]
    public function detailEvent(
        int $id,
        EventRepository $eventRepository
    )
    {
        $event = $eventRepository->findOneBy(['id' => $id]);

        if(!$event){
            return $this->redirectToRoute('app_events');
        }

        return $this->render('events/detailEvent.html.twig', [
            'event' => $event
        ]);
    }
}
