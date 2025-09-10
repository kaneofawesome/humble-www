<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/about', name: 'about')]
    public function __invoke(): Response
    {
        return $this->render('about/index.html.twig', [
            'pageTitle' => 'About Us - Humble',
            'cultureValues' => [
                [
                    'title' => 'High Bar',
                    'description' => 'We set ambitious standards and deliver exceptional quality in everything we create.'
                ],
                [
                    'title' => 'Low Ego',
                    'description' => 'We approach every challenge with humility, always ready to learn and grow.'
                ],
                [
                    'title' => 'Kind Candor',
                    'description' => 'We communicate with honesty and empathy, building trust through transparency.'
                ],
                [
                    'title' => 'Customer‑Obsessed',
                    'description' => 'Every decision we make starts with understanding and serving our customers\' needs.'
                ]
            ]
        ]);
    }
}