<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RootController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function index(): Response
    {
        return $this->render('root/index.html.twig', [
            'controller_name' => 'RootController',
        ]);
    }

    #[Route('/org/control_panel', name: 'app_control_panel')]
    public function controlPanel(): Response
    {
        if (!$this->getUser()->isOrganizer()) {
            return $this->redirectToRoute('app_root');
        }
        return $this->render('org/control_panel.html.twig', [
            'controller_name' => 'RootController',
        ]);
    }
}
