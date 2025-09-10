<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ComingSoonController extends AbstractController
{
    #[Route('/coming-soon', name: 'coming_soon')]
    public function __invoke(): Response
    {
        return $this->render('coming-soon/index.html.twig', [
            'pageTitle' => 'Coming Soon - Humble',
            'lightImageUrl' => '/images/coming-soon-wizard-brand-light.svg',
            'darkImageUrl' => '/images/coming-soon-wizard-brand-dark.svg',
        ]);
    }
}