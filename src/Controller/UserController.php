<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilForm;
use App\Service\ImageUploader;
use App\Form\ChangePasswordForm;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
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

        $form = $this->createForm(ProfilForm::class, $user);
        $changePasswordForm = $this->createForm(ChangePasswordForm::class, $user);

        $form->handleRequest($request);
        $changePasswordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ville = $request->request->get('city'); // récupère la valeur du select "city"
            $user->setCity($ville);

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
        
}
