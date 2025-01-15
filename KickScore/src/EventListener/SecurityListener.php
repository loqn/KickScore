<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityListener
{
    private RouterInterface $router;
    private TokenStorageInterface $tokenStorage;
    private Security $security;

    public function __construct(
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        Security $security
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->tokenStorage->setToken(null);

            $response = new RedirectResponse(
                $this->router->generate('app_login')
            );

            $event->setResponse($response);
        }
    }
}
