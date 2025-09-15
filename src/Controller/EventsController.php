<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Photo;
use App\Entity\Favori;
use App\Form\NewEventForm;
use App\Data\SearchDataEvent;
use App\Form\SearchFormEvent;
use App\Service\ImageUploader;
use App\Service\SearchService;
use App\Entity\RegistrationEvent;
use App\Repository\EventRepository;
use App\Repository\PhotoRepository;
use App\Repository\FavoriRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\RegistrationEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(
        EventRepository $eventRepository, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        ImageUploader $imageUploader,
        SearchService $searchService): Response
    {
        

        $result = $searchService->handleSearch(
            $request,
            new SearchDataEvent(),
            SearchFormEvent::class,
            fn($data) => $eventRepository->findSearch($data),
            [
                'content' => 'events/_events.html.twig',
                'pagination' => 'events/_pagination.html.twig',
            ],
            fn($data) => $eventRepository->findMinMax($data)
        );

        // Si c'est une requête AJAX, on a déjà une JsonResponse
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $event = new Event();
        $user = $this->getUser();
        $newEventForm = $this->createForm(NewEventForm::class, $event, [
            'allow_extra_fields' => true
        ]);
        $newEventForm->handleRequest($request);
        $event->setOrganizer($user);

        if($newEventForm->isSubmitted() && $newEventForm->isValid()){
           
            // Uploader les fichiers
            $photos = $newEventForm->get('photos')->getData(); 
            $filenames = $imageUploader->upload($photos, null, 'event');

            // récuperer la ville depuis extraData
            $extraData = $newEventForm->getExtraData();
            if (isset($extraData['city'])) {

                $event->setCity($extraData['city']);
            }
            
            $event->setCreatedAt(new \DateTime());

            foreach ($filenames as $filename) {
                $eventImage = new Photo(); 
                $eventImage->setUrl('upload/' . $filename);
                $eventImage->setEvent($event);
                $entityManager->persist($eventImage);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Course créé avec succès');
            return $this->redirectToRoute('app_events');
        }


        return $this->render('events/index.html.twig', [
            'newEventForm' => $newEventForm,
            'results' => $result['results'],
            'form' => $result['form'],
            'min' => $result['min'],
            'max' => $result['max'],
        ]);
    }

    #[Route('/events/lastEvents', name: 'app_lastEvents')]
    public function lastEvents(
        Request $request,
        EventRepository $eventRepository
    )
    {
        $data = new SearchDataEvent();
        $data->page = $request->get('page', 1);

        $qb = $eventRepository->getSearchLastEvents($data);
        $events = $eventRepository->findSearchLast($data, $qb);

        return $this->render('events/lastEvents.html.twig', [
            'results' => $events
        ]);
    }

    #[Route('/event/detailEvent/{id}', name: 'app_detailEvent')]
    public function detailEvent(
        int $id,
        EventRepository $eventRepository,
        PhotoRepository $photoRepository,
        RegistrationEventRepository $registrationEventRepository,
        FavoriRepository $favoriRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        ImageUploader $imageUploader
    )
    {
        // On récupere l'evenement
        $event = $eventRepository->findOneBy(['id' => $id]);
        if(!$event){
            return $this->redirectToRoute('app_events');
        }
        
        $photos = $photoRepository->findBy(['event' => $event]);
        $nbrInscription = $registrationEventRepository->countByEvent($id);
        $listInscrits = $registrationEventRepository->findBy(['event' => $event]);
        $listFavoris = $favoriRepository->findBy(['event' => $event]);
        $today = new \DateTime('today');
        
        $newEventForm = $this->createForm(NewEventForm::class, $event, [
            'allow_extra_fields' => true
        ]);
        $newEventForm->handleRequest($request);

        if($newEventForm->isSubmitted() && $newEventForm->isValid()) {
            // récuperer la ville depuis extraData
            $extraData = $newEventForm->getExtraData();
            if (isset($extraData['city'])) {

                $event->setCity($extraData['city']);
            }
            // Recupere les fichiers uploades depuis le formulaire
            $photos = $newEventForm->get('photos')->getData(); 
            //Upload de nouveux fichiers (on ne supprime rien)
            $filenames = $imageUploader->upload($photos, null, 'event');
            // Pour chaque nouveau fichier on crée une entite Photo lié à l'evenement
            foreach ($filenames as $filename) {
                $eventImage = new Photo(); 
                $eventImage->setUrl('upload/' . $filename);
                $eventImage->setEvent($event);
                $entityManager->persist($eventImage);
                $this->addFlash('success', 'Photo changé');
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_detailEvent', ['id'=> $event->getId()]);
        }

        return $this->render('events/detailEvent.html.twig', [
            'event' => $event,
            'photos' => $photos,
            "nbrInscription" => $nbrInscription,
            'listInscrits' => $listInscrits,
            'listFavoris' => $listFavoris,
            'newEventForm' => $newEventForm,
            'today' => $today
        ]);
    }

    #[Route('/photo/delete/{id}', name: 'app_photo_delete', methods: ['POST'])]
    public function deletePhoto(
        Photo $photo,
        EntityManagerInterface $em,
        Request $request): JsonResponse
    {
        $user = $this->getUser();
        // Vérification du droit sur la photo (à adapter selon ta logique)
        if ($photo->getEvent()->getOrganizer() !== $user) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $photo->getId(), $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        }

        $filepath = $this->getParameter('image_directory') . '/' . basename($photo->getUrl());
        if (file_exists($filepath) && !unlink($filepath)) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression du fichier'], 500);
        }

        $em->remove($photo);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }



    #[Route('/event/{id}/cancel', name: 'app_event_cancel', methods: ['POST'])]
    public function cancelEvent(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $em,
        Request $request): Response{
        $event = $eventRepository->find($id);

        if (!$event) {
            $this->addFlash('error', 'Cette course n\'existe pas');
            return $this->redirectToRoute('app_events');
        }

        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('cancel' . $event->getId(), $submittedToken)) {
            $event->setCancelled(true);
            $em->flush();

            $this->addFlash('success', 'La course a été annulée avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}/inscription', name: 'app_subscribeEvent', methods: ['POST'])]
    #[IsGranted('EMAIL_VERIFIED')]
    public function inscription(
        Event $event, 
        Request $request, 
        EntityManagerInterface $em,
        RegistrationEventRepository $registrationEventRepository): Response
    {

        $user = $this->getUser();
        if (! $user->isVerified()){
            $this->addFlash('warning', 'Tu dois confirmer ton email pour t\'inscrire à la course');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('inscription' . $event->getId(), $submittedToken)) {
            $this->addFlash('error', 'Token CSRF Invalide');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }        
        

        // Vérifie si la course est annulée
        if ($event->isCancelled()) {
            $this->addFlash('error', 'Impossible de s’inscrire : la course est annulée.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }
        
        // Vérifie si la course est complète (selon le nombre maximum de participants)
        $nbrInscription = $registrationEventRepository->countByEvent($event->getId());
        if ($nbrInscription >= $event->getCapacity()) {
            $this->addFlash('error', 'La course est complète.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

        // Vérifie si la date limite d’inscription est passée
        if ($event->getDateEvent() < new \DateTime()) {
            $this->addFlash('error', 'La course '.$event->getName().' est déjà passée.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

         // Vérifie si l’utilisateur est déjà inscrit
        $dejaInscrit = $registrationEventRepository->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($dejaInscrit) {
            $this->addFlash('info', 'Vous êtes déjà inscrit à cette course.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

        $inscription = new RegistrationEvent();
        $inscription->setEvent($event);
        $inscription->setUser($this->getUser());
        $inscription->setCreatedAt(new \DateTime());

        $em->persist($inscription);
        $em->flush();

        $this->addFlash('success', sprintf("Inscription à la course '%s' réussie !", $event->getName()));

        return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}/desinscription', name: 'app_unsubscribeEvent', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unsubscribe(
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        RegistrationEventRepository $registrationEventRepository
    ): Response {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('desinscription' . $event->getId(), $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

        $user = $this->getUser();

        // Vérifie si l’utilisateur est inscrit
        $inscription = $registrationEventRepository->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if (!$inscription) {
            $this->addFlash('info', 'Vous n\'êtes pas inscrit à cette course.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

        // Vérifie si la date limite d’inscription est passée
        if ($event->getDateEvent() < new \DateTime()) {
            $this->addFlash('error', 'La course '. $event->getname() .' est déjà passée.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

        // Supprime l’inscription
        $em->remove($inscription);
        $em->flush();

        $this->addFlash('success', 'Vous avez été désinscrit de la course.');
        return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}/favori', name: 'app_toggle_favori', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function toggleFavori(Event $event, Request $request, EntityManagerInterface $em, FavoriRepository $favoriRepository): Response
    {
        $user = $this->getUser();

        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('favori' . $event->getId(), $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
        }

        // Vérifie si déjà en favori
        $favori = $favoriRepository->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($favori) {
            $em->remove($favori);
            $message = 'Course retirée des favoris.';
        } else {
            $favori = new Favori();
            $favori->setEvent($event);
            $favori->setUser($user);
            $favori->setCreatedAt(new \DateTime());
            $em->persist($favori);
            $message = 'Course ajoutée aux favoris.';
        }

        $em->flush();
        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_detailEvent', ['id' => $event->getId()]);
    }
}