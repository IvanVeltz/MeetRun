<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginRateLimitListener
{
    private RouterInterface $router;
    private SessionInterface $session;

    public function __construct(RouterInterface $router, SessionInterface $session)
    {
        $this->router = $router;
        $this->session = $session;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof TooManyRequestsHttpException) {
            $this->session->getFlashBag()->add('error', 'Trop de tentatives de connexion. Réessayez dans quelques minutes.');
            $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
        }
    }
}
