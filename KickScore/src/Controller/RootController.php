<?php

namespace App\Controller;

use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/match', name: 'app_match_list')]
    public function matchList(EntityManagerInterface $entityManager): Response
    {
        $matches = $entityManager->getRepository(Versus::class)->findAll();

        return $this->render('match/match.html.twig', [
            'controller_name' => 'RootController',
            'matches' => $matches
        ]);
    }
}
