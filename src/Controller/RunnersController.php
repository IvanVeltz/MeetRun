<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchForm;
use App\Repository\UserRepository;
use App\Repository\LevelRunRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class RunnersController extends AbstractController
{
    #[Route('/runners', name: 'app_runners')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(UserRepository $userRepository, Request $request, LevelRunRepository $levelRunRepository): Response
    {

        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchForm::class, $data);
        // On récupère les coureurs
        $form->handleRequest($request);
        [$min, $max] = $userRepository->findMinMax($data);
        $runners = $userRepository->findSearch($data);

        if ($request->get('ajax')) {
            // Simule le rendu d'une vue partielle
            $content = $this->renderView('runners/_runners.html.twig', ['runners' => $runners]);
            $sorting = $this->renderView('runners/_sorting.html.twig', ['runners' => $runners]);
            $pagination = $this->renderView('runners/_pagination.html.twig', ['runners' => $runners]);
    
            return new JsonResponse([
                'content' => $content,
                'sorting' => $sorting,
                'pagination' => $pagination
            ]);
        }
        return $this->render('runners/index.html.twig', [
            'runners' => $runners,
            'form' => $form,
            'min' => $min,
            'max' => $max
        ]);
    }
}
