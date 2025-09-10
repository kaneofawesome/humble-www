<?php

namespace App\Controller;

use App\Service\HomeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly HomeService $homeService
    ) {
    }

    #[Route('/', name: 'home')]
    public function __invoke(): Response
    {
        $homePageData = $this->homeService->getHomePageData();

        return $this->render('home/index.html.twig', [
            'pageTitle' => $homePageData['title'],
            'heroImageUrl' => $homePageData['heroImageUrl'],
            'brandColors' => $homePageData['brandColors'],
        ]);
    }
}
