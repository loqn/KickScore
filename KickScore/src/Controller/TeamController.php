<?php

namespace App\Controller;

use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class TeamController extends AbstractController
{
    #[Route('/team', name: 'app_team')]
    public function index(): Response
    {
        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
        ]);
    }

    #[Route('/team/create', name: 'app_team_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException("organizers can't create teams.");
        }
        // Get form data
        $name = $request->request->get('name');
        $structure = $request->request->get('structure');
        $win = $request->request->get('win');
        $draw = $request->request->get('draw');
        $lose = $request->request->get('lose');

        if (empty($name)) {
            $this->addFlash('error', 'Name is required.');
            return $this->redirectToRoute('app_team');
        }
        // Create team
        $team = new Team();
        $team->setName($name);
        $team->setStructure($structure);
        $team->setWin($win);
        $team->setDraw($draw);
        $team->setLose($lose);
        $points = 3*$win + $draw;
        $gameplayed = $win + $draw + $lose;
        $team->setPoints($points);
        $team->setGamePlayed($gameplayed);


        // Handle match-specific data if it's a match
        $entityManager->persist($team);
        $entityManager->flush();
        $this->addFlash('success', 'team added successfully');
        return $this->redirectToRoute('app_team');
    }
}
