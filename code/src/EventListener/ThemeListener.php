<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class ThemeListener
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $theme = $request->cookies->get('theme', 'light');

        // Make theme available to all Twig templates
        $this->twig->addGlobal('user_theme', $theme);
    }
}
