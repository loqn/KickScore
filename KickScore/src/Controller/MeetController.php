<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MeetController extends AbstractController
{
    #[Route('/meet', name: 'app_meet')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can view meets.');
        }
        $championships = $entityManager->getRepository(Championship::class)->findAll();
            $teams = $entityManager->getRepository(Team::class)->findAll();
        return $this->render('meet/index.html.twig', [
            'controller_name' => 'MeetController',
            'championships' => $championships,
            'teams' => $teams,
        ]);
    }

    #[Route('/meet/create', name: 'app_meet_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can create meets.');
        }

        $addType = $request->request->get('addType');
        if (empty($addType)) {
            $this->addFlash('error', 'Name is required.');
            return $this->redirectToRoute('app_meet');
        }
        switch ($addType) {
            case "CHAMPIONSHIP":

                $logger->error('Championship creation requested.');
                $name = $request->request->get('name');
                $championship = new Championship();
                $championship->setName($name);
                $championship->setOrganizer($this->getUser());
                $entityManager->persist($championship);
                break;
            case "MATCH":
                $logger->error('Match creation requested.');
                $match = new Versus();
                $numChamp = $request->request->get('champ');
                $championship = $entityManager->getRepository(Championship::class)->find($numChamp);
                if (!$championship) {
                    $this->addFlash('error', 'Championship not found.');
                    return $this->redirectToRoute('app_meet');
                }
                $greenTeamId = $request->request->get('greenTeam');
                $greenTeam = $entityManager->getRepository(Team::class)->find($greenTeamId);
                $blueTeamId = $request->request->get('blueTeam');
                $blueTeam = $entityManager->getRepository(Team::class)->find($blueTeamId);
                if (!$greenTeam || !$blueTeam) {
                    $this->addFlash('error', 'Teams not found.');
                    return $this->redirectToRoute('app_meet');
                }
                $match->setChampionship($championship);
                $championship->addMatch($match);
                $match->setGreenTeam($greenTeam);
                $match->setBlueTeam($blueTeam);
                $entityManager->persist($match);
                break;
        }
        $logger->error('Persisting changes.');
        $entityManager->flush();
        $this->addFlash('success', 'Championship created successfully!');
        return $this->redirectToRoute('app_meet');
    }
}