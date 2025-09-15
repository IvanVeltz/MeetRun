<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follow;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('EMAIL_VERIFIED')]
final class FollowController extends AbstractController
{
    #[Route('/follow/request/{id}', name: 'app_follow_request', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function followRequest(User $user, Request $request, EntityManagerInterface $em, FollowRepository $followRepository): Response
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
        $existing = $followRepository->findOneBy(['userSource' => $currentUser, 'userTarget' => $user]);
        if (!$existing) {
            $follow = new Follow();
            $follow->setUserSource($currentUser);
            $follow->setUserTarget($user);
            $follow->setFollowAccepted(false);
            $em->persist($follow);
            $em->flush();
        } elseif($existing->isFollowAccepted()){
            $this->add_flash('warning', 'Vous suivez déjà ce coureur');
            return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);

        } else{
            $this->add_flash('warning', 'Une demande de follow est déjà en cours');
            return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
        }

        return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
    }
 

    #[Route('/follow/accept/{id}', name: 'app_follow_accept', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function acceptFollow(Follow $follow, Request $request, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        // Vérifie que c'est bien le destinataire
        if ($follow->getUserTarget() !== $currentUser){
            return $this->redirectToRoute('app_profil', ['id' => $currentUser->getId()]);
        }

        // Vérifie le CSRF
        if (!$this->isCsrfTokenValid('follow-accept'.$follow->getUserSource()->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        // Vérifie si déjà accepté
        if ($follow->isFollowAccepted()){
            return $this->redirectToRoute('app_profil', ['id' => $currentUser->getId()]);
        }

        $follow->setFollowAccepted(true);
        $entityManager->persist($follow);
        $entityManager->flush();

        return $this->redirectToRoute('app_profil', ['id' => $currentUser->getId()]);
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
            $this->addFlash('error', 'Vous ne pouvez pas vous unfollow vous-même.');
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
