<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follow;
use App\Form\ProfilForm;
use App\Form\ChangePasswordForm;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\TopicRepository;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\RegistrationEventRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        $form = $this->createForm(ProfilForm::class, $user);
        $changePasswordForm = $this->createForm(ChangePasswordForm::class, $user);

        $form->handleRequest($request);
        $changePasswordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('pictureProfilUrl')->getData(); // Récupérer le fichier
            $filesystem = new Filesystem();


            if ($imageFile) {
                $mimeType = $imageFile->getMimeType();
                if ($mimeType === "image/jpeg" || $mimeType === "image/png") {
                    $fileName = 'user-' . uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move('upload/', $fileName); // Déplace l’image

                    // Supprimer l'ancienne image si elle existe
                    $oldImage = $user->getPictureProfilUrl();
                    if ($oldImage && $filesystem->exists($oldImage)) {
                        $filesystem->remove($oldImage);
                    }

                    // Met à jour l'entité utilisateur avec le chemin de l’image
                    $user->setPictureProfilUrl('upload/' . $fileName);
                }
            } else {
                // Supprimer l'ancienne image si elle existe
                $oldImage = $user->getPictureProfilUrl();
                if ($oldImage && $filesystem->exists($oldImage)) {
                    $filesystem->remove($oldImage);
                }
                $user->setPictureProfilUrl(null);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()){

            $oldPassword = $changePasswordForm->get('oldPlainPassword')->getData();
            $newPassword = $changePasswordForm->get('newPlainPassword')->getData();

            // On verifie si l'ancien mot de passe est le meme que celui enregistré
            if ($passwordHasher->isPasswordValid($user, $oldPassword)) {
                // On hache le nouveau mot de passe
                $passwordHash = $passwordHasher->hashPassword($user, $newPassword);
                // Et on le rentre en bdd 
                $user->setPassword($passwordHash);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Vous avez changé votre mot de passe avec succès.');
                return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
            }

            $this->addFlash('error', 'Échec de la modification du mot de passe. Veuillez réessayer.');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/index.html.twig', [
            'profilForm' => $form,
            'changePasswordForm' => $changePasswordForm
        ]);
    }

    #[Route('user/profil/{id}', name: 'app_profil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profil(
        int $id, 
        RegistrationEventRepository $registrationEventRepository, 
        UserRepository $userRepository, 
        PostRepository $postRepository,
        TopicRepository $topicRepository): response
    {
        $user = $userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirige si aucun utilisateur connecté
        }

        $registrationNextEvents = $registrationEventRepository->findByUserAndNextEvents($user);
        $registrationPastEvents = $registrationEventRepository->findByUserAndPastEvents($user);
        $lastPosts = $postRepository->findBy(['user' => $user], ['dateMessage' => 'DESC'], 5);
        $lastTopics = $topicRepository->findBy(['user' => $user], ['dateCreation' => 'DESC'], 3);
        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'registrationNextEvents' => $registrationNextEvents,
            'registrationPastEvents' => $registrationPastEvents,
            'lastPosts' => $lastPosts,
            'lastTopics' => $lastTopics
        ]);
    }
    

    #[Route('/follow/request/{id}', name: 'app_follow_request', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function followRequest(User $user, Request $request, EntityManagerInterface $em, FollowRepository $repo): Response
    {
        $currentUser = $this->getUser();

        // On Verifie que l'utilisateur ne se follow pas lui même
        if ($currentUser === $user) return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);

        // On verifie que le CSRF_token est bien conforme avec celui créé par le formulaire de la vue, si ce n'est
        // pas le cas on bloque la requete 
        if (!$this->isCsrfTokenValid('follow'.$user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        // On verifie que le follow n'existe pas encore
        $existing = $repo->findOneBy(['userSource' => $currentUser, 'userTarget' => $user]);
        if (!$existing) {
            $follow = new Follow();
            $follow->setUserSource($currentUser);
            $follow->setUserTarget($user);
            $follow->setFollowAccepted(false);
            $em->persist($follow);
            $em->flush();
        }

        return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
    }
 

    #[Route('/follow/accept/{id}', name: 'app_follow_accept', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function acceptFollow(User $user, Request $request, Follow $follow, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        // On verfie que ce soit bien la personne connecté qui accepte la demande
        if ($follow->getUserTarget() !== $currentUser){
            return $this->redirectToRoute('app_profil', ['id' => $this->getUser()->getId()]);
        }

        // On verifie que le CSRF_token est bien conforme avec celui créé par le formulaire de la vue, si ce n'est
        // pas le cas on bloque la requete 
        if (!$this->isCsrfTokenValid('follow-accept'.$user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        // On vérifie si la demande est déjà acceptée
        if ($follow->isFollowAccepted()){
            return $this->redirectToRoute('app_profil', ['id' => $currentUser->getId()]);
        }

        $follow->setFollowAccepted(true);
        $entityManager->persist($follow);
        $entityManager->flush();

        return $this->redirectToRoute('app_profil', ['id' => $this->getUser()->getId()]);
    }



    #[Route('/unfollow/request/{id}', name: 'app_unfollow_request', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unfollowRequest(User $user, EntityManagerInterface $entityManager, FollowRepository $followRepository, Request $request): Response
    {
        $currentUser = $this->getUser();

        // On verifie que ne se unfollow pas soi-meme
        if ($currentUser === $user){
            $this->addFlash('error', 'Vous ne pouvez pas vous désabonner de vous-même.');
            return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
        }

        // On verifie que le CSRF_token est bien conforme avec celui créé par le formulaire de la vue, si ce n'est
        // pas le cas on bloque la requete 
        if (!$this->isCsrfTokenValid('unfollow'.$user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        // On vérifie que le follow existe bien
        $follow = $followRepository->findOneBy([
            'userSource' => $currentUser,
            'userTarget' => $user
        ]);

        // Si oui, on le supprime
        if ($follow){
            $entityManager->remove($follow);
            $entityManager->flush(); 
            $this->addFlash('success', 'Vous vous êtes désabonné avec succès.');
        } else {
            $this->addFlash('info', 'Vous ne suivez pas cet utilisateur.');
        }
        
        // Redirection vers la page précédente, car on peut unfollow de plusieurs pages differentes
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        // Redirection par defaut si le navigateur n'a pas de referer
        return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
    }

    #[Route('/unfollower/request/{id}', name: 'app_unfollower_request',methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unfollowerRequest(
        User $user,
        EntityManagerInterface $entityManager, 
        FollowRepository $followRepository,
        Request $request): Response
    {
        $currentUser = $this->getUser();

        // On verifie que ne se unfollower pas soi-meme
        if ($currentUser === $user){
            $this->addFlash('error', 'Vous ne pouvez pas retirer votre propre abonnement.');
            return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
        }

        // On verifie que le CSRF_token est bien conforme avec celui créé par le formulaire de la vue, si ce n'est
        // pas le cas on bloque la requete 
        if (!$this->isCsrfTokenValid('unfollower'.$user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        // On vérifie que le follow existe bien
       $follow = $followRepository->findOneBy([
            'userSource' => $user,
            'userTarget' => $currentUser
        ]);

        if($follow){
            $entityManager->remove($follow);
            $entityManager->flush();
            $this->addFlash('success', 'Cet utilisateur ne vous suit plus.');
        } else {
            $this->addFlash('info', 'Cet utilisateur ne vous suivait pas.');
        }

        // Redirection vers la page précédente si possible
        $referer = $request->headers->get('referer');
        if ($referer && str_starts_with($referer, $request->getSchemeAndHttpHost())) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_profil', ['id' => $currentUser->getId()]);
    }

    
}
