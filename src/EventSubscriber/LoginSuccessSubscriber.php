<?php
// src/EventSubscriber/LoginSuccessSubscriber.php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $em
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getAuthenticatedToken()->getUser();

        // Recharge l’objet pour être sûr d’avoir les dernières valeurs
        $user = $this->em->getRepository(User::class)->find($user->getId());

        //
        if ($user->isFirstConnection()) {           
            // Page d’on‑boarding ou profil
            $response = new RedirectResponse($this->router->generate('app_user'));

            
            $user->setFirstConnection(false);
            $this->em->persist($user);
            $this->em->flush();

            $event->setResponse($response);         // ⬅️ redirection forcée
            return;                                 // on s’arrête là
        }

        if (method_exists($user, 'getId')) {
            $targetUrl = $this->router->generate('app_profil', [
                'id' => $user->getId(),
            ]);
            $event->setResponse(new RedirectResponse($targetUrl));
        }
    }
}
