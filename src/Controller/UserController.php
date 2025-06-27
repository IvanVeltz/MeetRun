<?php

namespace App\Controller;

use App\Form\ProfilForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(Request $request,Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirige si aucun utilisateur connecté
        }

        $form = $this->createForm(ProfilForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $imageFile = $form->get('pictureProfilUrl')->getData(); // Récupéré le fichier
            $filesystem = new Filesystem();

            
            if($imageFile) {
                $mimeType = $imageFile->getMimeType();
                if ($mimeType === "image/jpeg" || $mimeType === "image/png") {
                    $fileName = 'user-'.uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move('upload/', $fileName); // Déplace l’image

                    // Supprimer l'ancienne image si elle existe
                    $oldImage = $user->getPictureProfilUrl();
                    if ($oldImage && $filesystem->exists($oldImage)){
                        $filesystem->remove($oldImage);
                    }
                    
                    // Met à jour l'entité utilisateur avec le chemin de l’image
                    $user->setPictureProfilUrl('upload/' . $fileName);
                }
            } else {
                // Supprimer l'ancienne image si elle existe
                $oldImage = $user->getPictureProfilUrl();
                if ($oldImage && $filesystem->exists($oldImage)){
                    $filesystem->remove($oldImage);
                }
                $user->setPictureProfilUrl(null);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/index.html.twig', [
            'profilForm' => $form,
        ]);
    }
}
