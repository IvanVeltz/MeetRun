<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    private RouterInterface $router;
    private RateLimiterFactory $loginLimiter;

    public function __construct(RouterInterface $router, RateLimiterFactory $loginLimiter)
    {
        $this->router = $router;
        $this->loginLimiter = $loginLimiter;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        // Limiter par email ou IP
        $limiter = $this->loginLimiter->create($email);
        $limit = $limiter->consume(1);
        if (!$limit->isAccepted()) {
            throw new CustomUserMessageAuthenticationException('Trop de tentatives. Réessayez plus tard.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // récupération du target path si existant
        $targetPath = $request->getSession()->get('_security.' . $firewallName . '.target_path');

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

    // Méthode obligatoire pour AbstractLoginFormAuthenticator
    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }
}
