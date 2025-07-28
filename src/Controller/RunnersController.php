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

        // Sinon, on rend la page classique
        return $this->render('runners/index.html.twig', [
            'results' => $result['results'],
            'form' => $result['form'],
            'min' => $result['min'],
            'max' => $result['max'],
        ]);
        
        // $data = new SearchData(); // On instancie un objet contenant les criteres de recherches
        // $data->page = $request->get('page', 1); 
        
        // // On créé le formulaire basé sur SearchForm
        // $form = $this->createForm(SearchForm::class, $data);
        // $form->handleRequest($request);
        
        // //Récupération des âges minimum et maximum présentes en base de données pour l'ajustement du slider
        // [$min, $max] = $userRepository->findMinMax($data);
        
        // $runners = $userRepository->findSearch($data);
        // // Si la requete est en AJAX, on renvoie les fragments HTML au format JSON
        // if ($request->get('ajax')) {
        //     // Simule le rendu d'une vue partielle
        //     $content = $this->renderView('runners/_runners.html.twig', ['runners' => $runners]);
        //     $sorting = $this->renderView('runners/_sorting.html.twig', ['runners' => $runners]);
        //     $pagination = $this->renderView('runners/_pagination.html.twig', ['runners' => $runners]);
    
        //     return new JsonResponse([
        //         'content' => $content,
        //         'sorting' => $sorting,
        //         'pagination' => $pagination
        //     ]);
        // }

        // // Requête classique (non AJAX) => on rend la page complète
        // return $this->render('runners/index.html.twig', [
        //     'runners' => $runners,
        //     'form' => $form,
        //     'min' => $min,
        //     'max' => $max
        // ]);
    }
}
