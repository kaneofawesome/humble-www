<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServicesController extends AbstractController
{
    #[Route('/services', name: 'services')]
    public function __invoke(): Response
    {
        return $this->render('services/index.html.twig', [
            'pageTitle' => 'Services - Humble',
            'services' => [
                [
                    'title' => 'Leadership & Career Coaching for Engineers',
                    'description' => 'Unlock your potential as both a technical professional and a leader. Our coaching helps engineers develop the soft skills, leadership capabilities, and personal growth mindset needed to excel in today\'s collaborative tech environment.',
                    'features' => [
                        'One-on-one coaching sessions tailored to engineers',
                        'Leadership development and team management skills',
                        'Career progression guidance',
                        'Communication and interpersonal skills enhancement',
                        'Goal setting and personal growth strategies'
                    ]
                ],
                [
                    'title' => 'Electronics & Code Project Consulting',
                    'description' => 'Bring your electronic and software projects to life with expert technical guidance. From circuit design and embedded systems, to high-level application logic, we provide precision engineering and clean coding expertise to turn your ideas into reality.',
                    'features' => [
                        'Circuit design and PCB layout optimization',
                        'Embedded systems programming and firmware development',
                        'High level language software and web development',
                        'Code review and technical architecture guidance',
                        'Project planning and technical feasibility analysis',
                        'Code quality best practices guidance',
                        'Troubleshooting and performance optimization'
                    ]
                ]
            ]
        ]);
    }
}
