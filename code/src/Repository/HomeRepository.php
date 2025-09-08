<?php

namespace App\Repository;

final class HomeRepository
{
    public function getFeaturedContent(): array
    {
        return [
            [
                'title' => 'Modern Design Solutions',
                'description' => 'Crafting beautiful and functional experiences that engage users and drive results.',
                'icon' => 'design'
            ],
            [
                'title' => 'Technical Excellence',
                'description' => 'Building robust, scalable solutions with cutting-edge technology and best practices.',
                'icon' => 'tech'
            ],
            [
                'title' => 'Strategic Thinking',
                'description' => 'Combining creativity with data-driven insights to deliver meaningful business outcomes.',
                'icon' => 'strategy'
            ]
        ];
    }

    public function getHeroContent(): array
    {
        return [
            'headline' => 'Welcome to Humble',
            'subheadline' => 'Where innovation meets excellence',
            'callToAction' => 'Discover More'
        ];
    }
}