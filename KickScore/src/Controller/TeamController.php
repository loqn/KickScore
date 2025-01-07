<?php

namespace App\Controller;

use App\Entity\Player; // Add this import if not already present
use App\Entity\Team; // Add this import if not already present
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TeamController extends AbstractController
{
    #[Route('/team', name: 'app_team')]
    public function index(EntityManagerInterface $entityManager,Request $request): Response
    {
        $players = $entityManager->getRepository(User::class)->findAll();

        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'players' => $players
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

        // get all the players registered

        // Handle match-specific data if it's a match
        $entityManager->persist($team);
        $entityManager->flush();
        $this->addFlash('success', 'team added successfully');
        return $this->redirectToRoute('app_team');
    }
}
