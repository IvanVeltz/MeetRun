<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class SearchService{
    
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Environment $twig 
    ) {}
    

    public function handleSearch(
        Request $request,
        object $searchData,
        string $formTypeClass,
        callable $findSearch,
        array $templates,
        callable $findMinMax
    ):array|JsonResponse
    {
        // Recuperation de la page
        $searchData->page = $request->get('page', 1);

        //Création et traitement du formulaire
        $form = $this->formFactory->create($formTypeClass, $searchData);
        $form->handleRequest($request);

        //Execution de la recherche
        $results = $findSearch($searchData);

        
        // Recherche du min/max
        [$min, $max] = $findMinMax($searchData);

        if ($request->get('ajax')) {

            $response = [
                'content' => $this->twig->render($templates['content'], ['results' => $results]),
                'pagination' => $this->twig->render($templates['pagination'], ['results' => $results])
            ];

            if(isset($templates['sorting'])){
                $response['sorting'] = $this->twig->render($templates['sorting'], ['results' => $results]);
            }

            return new JsonResponse($response);
            
        }
        
        // Préparation du retour pour une requête normale
        $responseData = [
            'form' => $form->createView(),
            'results' => $results,
            'min' => $min,
            'max' => $max
        ];

        return $responseData;
    }
}