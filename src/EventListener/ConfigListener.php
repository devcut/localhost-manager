<?php

namespace App\EventListener;

use App\Service\LocalhostManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class ConfigListener implements EventSubscriberInterface
{
    private $router;
    private $lm;
    private $defaultLocale;

    public function __construct(RouterInterface $router, LocalhostManager $lm, $defaultLocale = 'en')
    {
        $this->router = $router;
        $this->lm = $lm;
        $this->defaultLocale = $defaultLocale;
    }

    public function setUserLocale(RequestEvent $event)
    {
        $request = $event->getRequest();
        $request->setLocale($request->getPreferredLanguage());
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->setUserLocale($event);

        $request = $event->getRequest();
        $filesystem = new Filesystem();

        if ($request->attributes->get('_route') === 'configuration') {
            return;
        }

        if (!$filesystem->exists($this->lm->getPath())) {
            $url = $this->router->generate('configuration');
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
            KernelEvents::EXCEPTION => ['onKernelRequest']
        ];
    }
}