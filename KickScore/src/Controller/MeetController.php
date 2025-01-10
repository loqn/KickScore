<?php

namespace App\Controller;

use App\Entity\TeamResults;
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
use App\Repository\TeamResultsRepository;

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
    public function import(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can import meets.');
        }

        $file = $request->files->get('file');

        if (!$file) {
            $this->addFlash('error', 'Aucun fichier sélectionné.');
            return $this->redirectToRoute('app_meet');
        }

        $jsonData = file_get_contents($file->getPathname());
        $championships = json_decode($jsonData, true);

        if (!$championships) {
            $this->addFlash('error', 'Format JSON invalide, vérifiez le fichier.');
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
        $this->addFlash('success', 'Le(s) championnat(s) ont été importé(s) avec succès.');
        return $this->redirectToRoute('app_meet');
    }

    #[Route('/meet/create', name: 'app_meet_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER', null, 'Only organizers can create meets.');

        $addType = $request->request->get('addType');
        if (empty($addType)) {
            $this->addFlash('error', 'Le type est obligatoire.');
            return $this->redirectToRoute('app_meet');
        }

        try {
            match ($addType) {
                'CHAMPIONSHIP' => $this->handleChampionshipCreation($request, $entityManager),
                'MATCH' => $this->handleMatchCreation($request, $entityManager),
                default => throw new \InvalidArgumentException('Type non valide.')
            };

            $logger->debug('Persisting changes.');
            $entityManager->flush();

            return $this->redirectToRoute('app_meet');
        } catch (\Exception $e) {
            $logger->error('Error creating meet: ' . $e->getMessage());
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_meet');
        }
    }

    private function handleChampionshipCreation(Request $request, EntityManagerInterface $entityManager): void
    {
        $name = $request->request->get('name');
        if (empty($name)) {
            throw new \InvalidArgumentException('Le nom du championnat est obligatoire.');
        }

        $championship = new Championship();
        $championship->setName($name)
            ->setOrganizer($this->getUser());

        $entityManager->persist($championship);
        $this->addFlash('success', 'Championnat créé avec succès.');
    }

    private function handleMatchCreation(Request $request, EntityManagerInterface $entityManager): void
    {
        $championship = $this->getChampionship($request, $entityManager);
        [$greenTeam, $blueTeam] = $this->getTeams($request, $entityManager);

        $match = $this->createMatch($championship, $greenTeam, $blueTeam);

        if ($this->hasScores($request)) {
            $this->processMatchResults(
                $match,
                $championship,
                $greenTeam,
                $blueTeam,
                $request->request->get('greenscore'),
                $request->request->get('bluescore'),
                $entityManager
            );
        }

        $match->setDate(new \DateTime($request->request->get('date')));
        $entityManager->persist($match);
        $this->addFlash('success', 'Match créé avec succès.');
    }

    private function getChampionship(Request $request, EntityManagerInterface $entityManager): Championship
    {
        $championship = $entityManager->getRepository(Championship::class)->find(
            $request->request->get('champ')
        );

        if (!$championship) {
            throw new \InvalidArgumentException('Championnat sélectionné non trouvé.');
        }

        return $championship;
    }

    private function getTeams(Request $request, EntityManagerInterface $entityManager): array
    {
        $greenTeam = $entityManager->getRepository(Team::class)->find($request->request->get('greenTeam'));
        $blueTeam = $entityManager->getRepository(Team::class)->find($request->request->get('blueTeam'));

        if (!$greenTeam || !$blueTeam) {
            throw new \InvalidArgumentException('Équipe(s) manquante(s)');
        }

        return [$greenTeam, $blueTeam];
    }

    private function createMatch(Championship $championship, Team $greenTeam, Team $blueTeam): Versus
    {
        $match = new Versus();
        $match->setChampionship($championship)
            ->setGreenTeam($greenTeam)
            ->setBlueTeam($blueTeam);

        $championship->addMatch($match);

        return $match;
    }

    private function hasScores(Request $request): bool
    {
        return $request->request->get('greenscore') !== ''
            && $request->request->get('bluescore') !== '';
    }

    private function processMatchResults(
        Versus $match,
        Championship $championship,
        Team $greenTeam,
        Team $blueTeam,
        int $greenScore,
        int $blueScore,
        EntityManagerInterface $entityManager
    ): void {
        $match->setGreenScore($greenScore)
            ->setBlueScore($blueScore);

        $resultGreen = $this->getTeamResults($greenTeam, $championship, $entityManager);
        $resultBlue = $this->getTeamResults($blueTeam, $championship, $entityManager);

        $this->updateTeamResults($resultGreen, $resultBlue, $greenScore, $blueScore);
    }

    private function getTeamResults(Team $team, Championship $championship, EntityManagerInterface $entityManager): TeamResults
    {
        return $entityManager->getRepository(TeamResults::class)->findOneBy([
            'team' => $team,
            'championship' => $championship
        ]);
    }

    private function updateTeamResults(
        TeamResults $resultGreen,
        TeamResults $resultBlue,
        int $greenScore,
        int $blueScore
    ): void {
        $resultGreen->setGamesPlayed($resultGreen->getGamesPlayed() + 1);
        $resultBlue->setGamesPlayed($resultBlue->getGamesPlayed() + 1);

        if ($greenScore > $blueScore) {
            $this->processWin($resultGreen, $resultBlue);
        } elseif ($greenScore < $blueScore) {
            $this->processWin($resultBlue, $resultGreen);
        } else {
            $this->processDraw($resultGreen, $resultBlue);
        }
    }

    private function processWin(TeamResults $winner, TeamResults $loser): void
    {
        $winner->setWins($winner->getWins() + 1)
            ->setPoints($winner->getPoints() + 3);

        $loser->setLosses($loser->getLosses() + 1);
    }

    private function processDraw(TeamResults $team1, TeamResults $team2): void
    {
        $team1->setDraws($team1->getDraws() + 1)
            ->setPoints($team1->getPoints() + 1);

        $team2->setDraws($team2->getDraws() + 1)
            ->setPoints($team2->getPoints() + 1);
    }

    #[Route('/meet/edit/{id}', name: 'edit_match', methods: ['GET'])]
    public function edit(Versus $match, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can edit meets.');
        }
        return $this->render('meet/edit.html.twig', [
            'match' => $match,
        ]);
    }

    //update_match
    #[Route('/meet/update/{id}', name: 'update_match', methods: ['POST'])]
    public function update(Request $request, Versus $match, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can update meets.');
        }
        $greenTeam = $entityManager->getRepository(Team::class)->find($request->request->get('greenTeam'));
        $blueTeam = $entityManager->getRepository(Team::class)->find($request->request->get('blueTeam'));
        $match->setGreenTeam($greenTeam);
        $match->setBlueTeam($blueTeam);
        $match->setGreenScore($request->request->get('greenScore'));
        $match->setBlueScore($request->request->get('blueScore'));
        $match->setDate(new \DateTime($request->request->get('date')));
        $entityManager->flush();
        $this->addFlash('success', 'Match mis à jour avec succès.');
        return $this->redirectToRoute('app_meet');
    }

    #[Route('/gen_match/{id}', name: 'app_generate_match', methods: ['POST'])]
    public function generateMatchsForChampionship(Request $request, EntityManagerInterface $entityManager): Response
    {
       $chp = $entityManager->getRepository(Championship::class)->find($request->request->get('chp_id'));
       [...]
        return $this->redirectToRoute('app_match_list');
    }
}