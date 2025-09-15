<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Form\ProfilForm;
use App\Service\ImageUploader;
use App\Form\ChangePasswordForm;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
use App\Repository\FollowRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\RegistrationEventRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(
        Request $request, 
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader, 
        UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        $form = $this->createForm(ProfilForm::class, $user, [
            'allow_extra_fields' => true
        ]);
        $changePasswordForm = $this->createForm(ChangePasswordForm::class, $user);

        $form->handleRequest($request);
        $changePasswordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $extraData = $form->getExtraData();
            if (isset($extraData['city'])) {

                $user->setCity($extraData['city']);
            }

            $imageFile = $form->get('pictureProfilUrl')->getData();
            $oldImage = $user->getPictureProfilUrl();

            $newFileName = $imageUploader->upload($imageFile, $oldImage, 'user');

            if (!empty($newFileName)) {
                $user->setPictureProfilUrl('upload/' . $newFileName[0]);
            } else {
                $user->setPictureProfilUrl(null);
            }
            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil modifié avec succes');
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
        User $other,
        RegistrationEventRepository $registrationEventRepository, 
        UserRepository $userRepository, 
        PostRepository $postRepository,
        TopicRepository $topicRepository,
        FollowRepository $followRepository,
        MessageRepository $messageRepository): response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        if (!$user) {
            $this->addFlash('warning', 'Utilisateur introuvable');
            return $this->redirectToRoute('app_home');
        }
        $currentUser = $this->getUser();

    


        $canMessage = $followRepository->areMutuallyFollowing($currentUser, $other);

        if(!$canMessage){
            $messages = [];
        } else {
            $messages = $messageRepository->findConversation($currentUser, $other, 30, 0);
        }

        $friends = $followRepository->findBy([
            'userSource' => $currentUser->getId(),
            'followAccepted' => 1
        ]);

        $actions = $userRepository->findLastactionByUser($user);

        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'messages' => $messages,
            'other' => $other,
            'canMessage' => $canMessage,
            'actions' => $actions,
            'friends' => $friends
        ]);
    }
    
    #[Route('/user/profil/{id}/messages/add', name:'app_chat_addMessage', methods:['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addMessage(
        User $other,
        Request $request,
        EntityManagerInterface $em,
        FollowRepository $followRepository
    ): Response {
        $user = $this->getUser();

        if(!$other){
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('app_home');
        }

        // Vérifie le follow mutuel
        if (!$followRepository->areMutuallyFollowing($user, $other)) {
            $this->addFlash('warning', 'Vous ne pouvez pas envoyer de message à cet utilisateur.');
            return $this->redirectToRoute('app_profil', ['id' => $other->getId()]);
        }

        // On vérifie le token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('createChatMessage', $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        }

        $content = trim(filter_var(
            $request->request->get('chatMessage'),
            FILTER_UNSAFE_RAW,
            FILTER_FLAG_NO_ENCODE_QUOTES
        ));

        if (!$content) {
            $this->addFlash('warning', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('app_profil', ['id' => $other->getId()]);
        }

        $message = new Message();
        $message->setSender($user);
        $message->setRecipient($other);
        $message->setContent($content);
        $message->setDateOfMessage(new \DateTime());

        $em->persist($message);
        $em->flush();

        $this->addFlash('success', 'Message envoyé');

        return $this->redirectToRoute('app_profil', ['id' => $other->getId()]);

    }
}
