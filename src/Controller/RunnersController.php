<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchForm;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class RunnersController extends AbstractController
{
    #[Route('/runners', name: 'app_runners')]
    public function index(UserRepository $userRepository, Request $request): Response
    {

        $data = new SearchData();
        $form = $this->createForm(SearchForm::class, $data);
        // On récupère les coureurs
        $form->handleRequest($request);
        $runners=[];
        if ($form->isSubmitted() && $form->isValid()) {
            $runners = $userRepository->findSearch($data);
        }


        return $this->render('runners/index.html.twig', [
            'runners' => $runners,
            'form' => $form
        ]);
    }
}
