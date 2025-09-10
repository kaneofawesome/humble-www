<?php

namespace App\Service;

use App\Repository\HomeRepository;

final class HomeService
{
    public function __construct(
        private readonly HomeRepository $homeRepository
    ) {
    }

    public function getHomePageData(): array
    {
        $featuredContent = $this->homeRepository->getFeaturedContent();

        return [
            'title' => 'Welcome to Humble',
            'heroImageUrl' => '/images/humble-wizards-logo.png',
            'brandColors' => [
                'primary' => '#B180F0',
                'secondary' => '#37B5B6', 
                'accent' => '#F8F8F8',
                'dark' => '#222222'
            ],
            'featuredContent' => $featuredContent,
            'navigationItems' => $this->getNavigationItems(),
        ];
    }

    private function getNavigationItems(): array
    {
        return [
            ['label' => 'Home', 'route' => 'home', 'active' => true],
            ['label' => 'About', 'route' => 'coming_soon', 'active' => false],
            ['label' => 'Services', 'route' => 'coming_soon', 'active' => false],
            ['label' => 'Portfolio', 'route' => 'coming_soon', 'active' => false],
            ['label' => 'Contact', 'route' => 'coming_soon', 'active' => false],
        ];
    }
}