<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions.html.twig');
    }

    #[Route('/politique-confidentialite', name: 'app_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('legal/confidentialite.html.twig');
    }

    #[Route('/conditions-utilisation', name: 'app_conditions_utilisation')]
    public function conditionsUtilisation(): Response
    {
        return $this->render('legal/conditions.html.twig');
    }
}
