<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ImpersonateController extends AbstractController
{
    #[Route('/exit-impersonation', name: 'app_exit_impersonation')]
    public function exitImpersonation(): RedirectResponse
    {
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/{id}/impersonate', name:'app_impersonate')]
    public function impersonate(Request $request, User $user): Response
    {
        $targetUrl = $this->generateUrl('app_root');
        return $this->redirect($targetUrl . '?_switch_user=' . $user->getMail());
    }
}
