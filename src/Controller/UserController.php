<?php

namespace App\Controller;

use App\Form\ProfilForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
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
            
            if($imageFile) {
                $mimeType = $imageFile->getMimeType();
                if ($mimeType === "image/jpeg" || $mimeType === "image/png") {
                    $fileName = $user->getId() . $user->getFirstName() . $user->getLastName() . '.' . $imageFile->guessExtension(); // Nom du fichier
                    $imageFile->move('img/', $fileName); // Déplace l’image
                    
                    // Met à jour l'entité utilisateur avec le chemin de l’image
                    $user->setPictureProfilUrl('img/' . $fileName);
                }
            } else {
                $user->setPictureProfilUrl('');
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
