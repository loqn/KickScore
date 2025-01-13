<?php

namespace App\Controller;

use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Championship;
use App\Entity\Team;
use App\Entity\TimeSlot;
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

                $blueTeam = $entityManager->getRepository(Team::class)->find($matchData['BlueTeam']);
                $greenTeam = $entityManager->getRepository(Team::class)->find($matchData['GreenTeam']);

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
    function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can create meets.');
        }
        $addType = $request->request->get('addType');
        if (empty($addType)) {
            $this->addFlash('error', 'Le nom est obligatoire.');
            return $this->redirectToRoute('app_meet');
        }
        switch ($addType) {
            case "CHAMPIONSHIP":
                $name = $request->request->get('name');
                $date_start = new \DateTime($request->request->get('date_start'));
                $date_end = new \DateTime($request->request->get('date_end'));
                if (empty($name)) {
                    $this->addFlash('error', 'Le nom du championnat est obligatoire.');
                    return $this->redirectToRoute('app_meet');
                }
                $championships = $entityManager->getRepository(Championship::class)->findAll();
                if ($championships) {
                    foreach ($championships as $chp) {
                        if ($chp->getStartDate() < $date_start && $chp->getEndDate() > $date_end) {
                            $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                            return $this->redirectToRoute('app_meet');
                        }
                        if ($chp->getStartDate() < $date_end && $chp->getEndDate() > $date_end) {
                            $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                            return $this->redirectToRoute('app_meet');
                        }
                        if ($chp->getStartDate() >= $date_start && $chp->getStartDate() <= $date_end) {
                            $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                            return $this->redirectToRoute('app_meet');
                        }
                        if ($chp->getEndDate() >= $date_start && $chp->getEndDate() <= $date_end) {
                            $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                            return $this->redirectToRoute('app_meet');
                        }
                    }
                }
                if ($date_start >= $date_end) {
                    $this->addFlash('error', 'Les dates ne sont pas cohérentes.');
                    return $this->redirectToRoute('app_meet');
                }
                $championship = new Championship();
                $championship->setName($name);
                $championship->setStartDate($date_start);
                $championship->setEndDate($date_end);
                $championship->setOrganizer($this->getUser());
                $entityManager->persist($championship);
                $this->addFlash('success', 'Championnat créé avec succès.');
                break;
            case "MATCH":
                $match = new Versus();
                $numChamp = $request->request->get('champ');
                $championship = $entityManager->getRepository(Championship::class)->find($numChamp);
                if (!$championship) {
                    $this->addFlash('error', 'Championnat sélectionné non trouvé.');
                    return $this->redirectToRoute('app_meet');
                }
                $greenTeamId = $request->request->get('greenTeam');
                $greenTeam = $entityManager->getRepository(Team::class)->find($greenTeamId);

                $blueTeamId = $request->request->get('blueTeam');
                $blueTeam = $entityManager->getRepository(Team::class)->find($blueTeamId);
                if (!$greenTeam || !$blueTeam) {
                    $this->addFlash('error', 'Équipe(s) manquante(s)');
                    return $this->redirectToRoute('app_meet');
                }
                $match->setChampionship($championship);
                $championship->addMatch($match);
                $greenTeam->addChampionship($championship);
                $blueTeam->addChampionship($championship);
                $match->setGreenTeam($greenTeam);
                $match->setBlueTeam($blueTeam);
                if ($request->request->get('greenscore') != "" && $request->request->get('bluescore') != "") {
                    $match->setGreenScore($request->request->get('greenscore'));
                    $match->setBlueScore($request->request->get('bluescore'));
                    if ($match->getGreenScore() > $match->getBlueScore()) {
                        $greenTeam->setWin($greenTeam->getWin() + 1);
                        $blueTeam->setLose($blueTeam->getLose() + 1);
                        $greenTeam->setPoints($greenTeam->getPoints() + 3);
                    } elseif ($match->getGreenScore() < $match->getBlueScore()) {
                        $blueTeam->setWin($blueTeam->getWin() + 1);
                        $greenTeam->setLose($greenTeam->getLose() + 1);
                        $blueTeam->setPoints($blueTeam->getPoints() + 3);
                    } else {
                        $greenTeam->setDraw($greenTeam->getDraw() + 1);
                        $blueTeam->setDraw($blueTeam->getDraw() + 1);
                        $greenTeam->setPoints($greenTeam->getPoints() + 1);
                        $blueTeam->setPoints($blueTeam->getPoints() + 3);
                    }
                }
                $match->setDate(new \DateTime($request->request->get('date')));
                $this->addFlash('success', 'Match créé avec succès.');
                $entityManager->persist($match);
                break;
        }
        $logger->debug('Persisting changes.');
        $entityManager->flush();

        return $this->redirectToRoute('app_meet');
    }

    #[Route('/gen_match/{id}', name: 'app_generate_match', methods: ['POST'])]
    public function generateMatchsForChampionship(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chp = $entityManager->getRepository(Championship::class)->find($request->request->get('chp_id'));
        $teams = $chp->getTeams();
        $teamsArray = $teams->toArray();
        $startDate = new \DateTimeImmutable('2026-12-12 10:00:00');
        $matchCount = 0;
        for ($i = 0; $i < count($teamsArray); $i++) {
            for ($j = $i + 1; $j < count($teamsArray); $j++) {
                $teamA = $teamsArray[$i];
                $teamB = $teamsArray[$j];
                $existingMatch = $chp->getMatches()->filter(function ($match) use ($teamA, $teamB) {
                    return ($match->getBlueTeam() === $teamA && $match->getGreenTeam() === $teamB)
                        || ($match->getBlueTeam() === $teamB && $match->getGreenTeam() === $teamA);
                })->first();
                if (!$existingMatch) {
                    $versus = new Versus();
                    $versus->setBlueTeam($teamA);
                    $versus->setGreenTeam($teamB);
                    $versus->setChampionship($chp);
                    $minutesToAdd = 20 * $matchCount;
                    $matchDate = $startDate->modify("+{$minutesToAdd} minutes");
                    $versus->setDate(\DateTime::createFromImmutable($matchDate));
                    $chp->addMatch($versus);
                    $entityManager->persist($versus);
                    $matchCount++;
                }
            }
        }
        $this->addFlash('success', 'Matchs générés avec succès.');
        $entityManager->flush();
        return $this->redirectToRoute('app_match_list');
    }

    #[Route('/meet/edit/{id}', name: 'edit_match', methods: ['GET'])]
    public function edit(Versus $match, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can edit meets.');
        }
        return $this->render('meet/edit.html.twig', [
            'match' => $match,
            'teams' => $match->getTeams()
        ]);
    }

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
        return $this->redirectToRoute('app_match_list');
    }
}