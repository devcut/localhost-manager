<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class ConfigListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
            KernelEvents::EXCEPTION => ['onKernelRequest']
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists(__DIR__ . '/../../config/localhost_manager.yaml') && $event->getRequest()->attributes->get('_route') !== 'configuration') {
            $url = $this->router->generate('configuration');
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }
}