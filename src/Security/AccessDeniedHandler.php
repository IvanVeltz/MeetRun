<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private RouterInterface $router;
    private TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;

    public function __construct(
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    public function handle($request, AccessDeniedException $accessDeniedException): ?RedirectResponse
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user && method_exists($user, 'isVerified') && !$user->isVerified()) {
                // Récupère la session depuis le RequestStack
                $session = $this->requestStack->getSession();
                $session->getFlashBag()->add(
                    'warning',
                    'Tu dois confirmer ton email pour accéder à cette page.'
                );

                return new RedirectResponse($this->router->generate('app_home'));
            }
        }

        return null; // Symfony renverra 403 si non géré
    }
}
