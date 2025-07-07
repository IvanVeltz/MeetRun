<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RunnersController extends AbstractController
{
    #[Route('/runners', name: 'app_runners')]
    public function index(): Response
    {
        return $this->render('runners/index.html.twig', [
            'controller_name' => 'RunnersController',
        ]);
    }
}
