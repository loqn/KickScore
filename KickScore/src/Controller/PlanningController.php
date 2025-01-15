<?php

namespace App\Controller;

use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Championship;
use App\Entity\Team;
use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'app_planning')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $championships = $entityManager->getRepository(Championship::class)->findAll();
        $teams = $entityManager->getRepository(Team::class)->findAll();
        return $this->render(
            'planning/index.html.twig',
            [
            'controller_name' => 'PlanningController',
            'championships' => $championships,
            'teams' => $teams,
            ]
        );
    }
}
