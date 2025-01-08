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

    #[Route('/meet/import', name: 'app_meet_import', methods: ['POST'])]
    #[Route('/meet/import', name: 'app_meet_import', methods: ['POST'])]
    public function import(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can import meets.');
        }
    
        $file = $request->files->get('file');
        
        if (!$file) {
            $this->addFlash('error', 'No file uploaded.');
            return $this->redirectToRoute('app_meet');
        }
    
        $jsonData = file_get_contents($file->getPathname());
        $championships = json_decode($jsonData, true);
    
        if (!$championships) {
            $this->addFlash('error', 'Invalid JSON format.');
            return $this->redirectToRoute('app_meet');
        }
    
        foreach ($championships as $championshipData) {
            $championship = new Championship();
            $championship->setName($championshipData['name']);
            $championship->setOrganizer($this->getUser());
            
            foreach ($championshipData['matches'] as $matchData) {
                $match = new Versus();
                $match->setChampionship($championship);
                
                // Get team entities from repository
                $blueTeam = $entityManager->getRepository(Team::class)->find($matchData['BlueTeam']);
                $greenTeam = $entityManager->getRepository(Team::class)->find($matchData['GreenTeam']);
                
                // Create teams if they don't exist
                if (!$greenTeam) {
                    $greenTeam = new Team();
                    $greenTeam->setName($matchData['GreenTeam']);
                    $entityManager->persist($greenTeam);
                }
                
                if (!$blueTeam) {
                    $blueTeam = new Team();
                    $blueTeam->setName($matchData['BlueTeam']);
                    $entityManager->persist($blueTeam);
                }
                $championship->addTeam($greenTeam);
                $championship->addTeam($blueTeam);
                $match->setBlueTeam($blueTeam);
                $match->setGreenTeam($greenTeam);
                $match->setBlueScore($matchData['BlueScore']);
                $match->setGreenScore($matchData['GreenScore']);
                $match->setDate(new \DateTime($matchData['Date']));
    
                $entityManager->persist($match);
            }
            
            $entityManager->persist($championship);
        }
    
        $entityManager->flush();
        $this->addFlash('success', 'Championships and matches have been imported successfully.');
        return $this->redirectToRoute('app_meet');
    }

    #[Route('/meet/create', name: 'app_meet_create', methods: ['POST'])]
    function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
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
                $name = $request->request->get('name');
                $championship = new Championship();
                $championship->setName($name);
                $championship->setOrganizer($this->getUser());
                $entityManager->persist($championship);
                break;
            case "MATCH":
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
                $greenTeam->setChampionship($championship);
                $blueTeam->setChampionship($championship);
                $match->setGreenTeam($greenTeam);
                $match->setBlueTeam($blueTeam);
                if ($request->request->get('greenscore') != "" && $request->request->get('bluescore') != "") {
                    $match->setGreenScore($request->request->get('greenscore'));
                    $match->setBlueScore($request->request->get('bluescore'));
                }
                $match->setDate(new \DateTime($request->request->get('date')));
                $entityManager->persist($match);
                break;
        }
        $logger->debug('Persisting changes.');
        $entityManager->flush();
        $this->addFlash('success', 'Championship created successfully!');
        return $this->redirectToRoute('app_meet');
    }
}