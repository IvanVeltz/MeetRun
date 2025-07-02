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
        // Depuis Symfony 6.3 : déclenché pour TOUT type d’authentification
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getAuthenticatedToken()->getUser();

        // Recharge l’objet pour être sûr d’avoir les dernières valeurs (facultatif)
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

        // Sinon, on le laisse aller à la page par défaut (app_home)
        //   → Ne rien faire = conserver la réponse générée par Symfony
    }
}
