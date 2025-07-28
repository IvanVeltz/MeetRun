<?php

namespace App\Controller;

use App\Data\SearchDataRunner;
use App\Form\SearchFormRunner;
use App\Service\SearchService;
use App\Repository\UserRepository;
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
    public function index(
        Request $request,
        SearchService $searchService,
        UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $result = $searchService->handleSearch(
            $request,
            new SearchDataRunner(),
            SearchFormRunner::class,
            fn($data) => $userRepository->findSearch($data, $this->getUser()),
            [
                'content' => 'runners/_runners.html.twig',
                'pagination' => 'runners/_pagination.html.twig',
                'sorting' => 'runners/_sorting.html.twig',
            ],
            fn($data) => $userRepository->findMinMax($data)
        );

        // Si c'est une requête AJAX, on a déjà une JsonResponse
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $userSuggested = $userRepository->findNearByUser($user->getLatitude(), $user->getLongitude(), 100, $user->getId());

        // Sinon, on rend la page classique
        return $this->render('runners/index.html.twig', [
            'results' => $result['results'],
            'form' => $result['form'],
            'min' => $result['min'],
            'max' => $result['max'],
            'userSuggested' => $userSuggested
        ]);
    }
}
