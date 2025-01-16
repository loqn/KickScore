<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Championship;
use App\Entity\Versus;
use App\Entity\Team;
use App\Entity\Field;


class LiveController extends AbstractController
{
    #[Route('/live', name: 'app_live')]
    public function index(EntityManagerInterface $entityManager,
    ): Response
    {
        //get all championships
        $championships = $entityManager->getRepository(Championship::class)->findAll();
        //get all matches
        $matches = $entityManager->getRepository(Versus::class)->findAll();
        //get all teams
        $teams = $entityManager->getRepository(Team::class)->findAll();
        //get all fields
        $fields = $entityManager->getRepository(Field::class)->findAll();
        return $this->render('live/index.html.twig', [
            'controller_name' => 'LiveController',
            'championships' => $championships,
            'matches' => $matches,
            
            'teams' => $teams,
            'fields' => $fields
        ]);
    }
}
