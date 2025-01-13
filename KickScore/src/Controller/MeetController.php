<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\TeamMatchStatus;
use App\Entity\Championship;
use App\Entity\Team;
use App\Entity\Timeslot;
use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class MeetController extends AbstractController
{
    #[Route('/gen_match/{id}', name: 'app_generate_match', methods: ['POST'])]
    public function generateMatchsForChampionship(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chp = $entityManager->getRepository(Championship::class)->find($request->request->get('chp_id'));

        //if start > now, start = tomorrow
        $realStart = $chp->getStartDate();

        if ($chp->getTeams()->isEmpty()) {
            $this->addFlash('error', 'Il n\'y a aucune équipe participante !');
            return $this->redirectToRoute('app_user_index');
        }
        if ($chp->getEndDate() < new \DateTime()) {
            $this->addFlash('error', 'Impossible de générer des matchs pour un championnat déjà terminé.');
            return $this->redirectToRoute('app_user_index');
        }
        if ($chp->getStartDate() < new \DateTime() && !$chp->getMatches()->isEmpty()) {
            $this->addFlash('error', 'Impossible de générer des matchs pour un championnat déjà commencé.');
            return $this->redirectToRoute('app_user_index');
        } else if ($chp->getStartDate() < new \DateTime() && $chp->getMatches()->isEmpty()) {
            $realStart = (new \DateTime())->modify('+1 day');
        }

        $teams = $chp->getTeams();
        $teamsArray = $teams->toArray();

        if ($chp->getFields()->isEmpty()) {
            $this->addFlash('error', 'Il n\'y a aucun terrain disponible pour programmer des matchs dans ce championnat.');
            return $this->redirectToRoute('app_user_index');
        }

        $fields = $chp->getFields();

        $timeSlots = $this->generateTimeSlots($realStart, $chp->getEndDate(), 30);
        if (empty($timeSlots)) {
            $this->addFlash('error', 'Impossible de générer des créneaux horaires.');
            return $this->redirectToRoute('app_user_index');
        }

        $totalMatches = (count($teamsArray) * (count($teamsArray) - 1)) / 2;
        if (count($timeSlots) * count($fields) < $totalMatches) {
            $this->addFlash('error', 'Pas assez de créneaux disponibles pour tous les matchs.');
            return $this->redirectToRoute('app_match_list');
        }

        $matchQueue = [];
        for ($i = 0; $i < count($teamsArray); $i++) {
            for ($j = $i + 1; $j < count($teamsArray); $j++) {
                $teamA = $teamsArray[$i];
                $teamB = $teamsArray[$j];

                $existingMatch = $chp->getMatches()->filter(function ($match) use ($teamA, $teamB) {
                    return ($match->getBlueTeam() === $teamA && $match->getGreenTeam() === $teamB)
                        || ($match->getBlueTeam() === $teamB && $match->getGreenTeam() === $teamA);
                })->first();

                if (!$existingMatch) {
                    $matchQueue[] = ['teamA' => $teamA, 'teamB' => $teamB];
                }
            }
        }

        $teamLastTimeSlot = array_fill_keys(array_map(fn($team) => $team->getId(), $teamsArray), null);

        foreach ($timeSlots as $timeSlot) {
            $entityManager->persist($timeSlot);

            foreach ($fields as $field) {
                if (empty($matchQueue)) break 2;

                foreach ($matchQueue as $queueIndex => $matchPair) {
                    $teamA = $matchPair['teamA'];
                    $teamB = $matchPair['teamB'];

                    if ($this->teamsCanPlayInTimeSlot($teamA, $teamB, $teamLastTimeSlot, $timeSlot)) {
                        $versus = new Versus();
                        $versus->setBlueTeam($teamA);
                        $versus->setGreenTeam($teamB);
                        $versus->setChampionship($chp);
                        $versus->setTimeSlot($timeSlot);
                        $versus->setDate($timeSlot->getStart());
                        $versus->setField($field);
                        $entityManager->persist($versus);

                        $matchStatus = $entityManager->getRepository(Status::class)->findOrCreate('COMING');
                        $versus->setStatus($matchStatus);

                        $teamStatusA = new TeamMatchStatus();
                        $teamStatusA->setTeam($teamA);
                        $teamStatusA->setVersus($versus);
                        $teamStatusA->setStatus($matchStatus);
                        $entityManager->persist($teamStatusA);

                        $teamStatusB = new TeamMatchStatus();
                        $teamStatusB->setTeam($teamB);
                        $teamStatusB->setVersus($versus);
                        $teamStatusB->setStatus($matchStatus);
                        $entityManager->persist($teamStatusB);

                        $teamLastTimeSlot[$teamA->getId()] = $timeSlot;
                        $teamLastTimeSlot[$teamB->getId()] = $timeSlot;

                        unset($matchQueue[$queueIndex]);
                        break;
                    }
                }
            }
        }

        if (!empty($matchQueue)) {
            $this->addFlash('error', 'Impossible de programmer tous les matchs dans les créneaux disponibles.');
            return $this->redirectToRoute('app_match_list', ['championship' => $chp->getId()]);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Matchs générés avec succès.');
        return $this->redirectToRoute('app_match_list', ['championship' => $chp->getId()]);
    }

    private function generateTimeSlots(\DateTime $startDate, \DateTime $endDate, int $duration): array
    {
        $timeSlots = [];
        $currentDate = clone $startDate;

        while ($currentDate < $endDate) {
            $dayStart = clone $currentDate;
            $dayStart->setTime(9, 0);

            $dayEnd = clone $currentDate;
            $dayEnd->setTime(22, 0);

            $currentSlot = clone $dayStart;

            while ($currentSlot < $dayEnd) {
                $timeSlot = new TimeSlot();
                $timeSlot->setStart(clone $currentSlot);

                $endTime = clone $currentSlot;
                $endTime->modify("+{$duration} minutes");
                $timeSlot->setEnd($endTime);

                $timeSlots[] = $timeSlot;
                $currentSlot->modify('+30 minutes');
            }

            $currentDate->modify('+1 day');
            $currentDate->setTime(0, 0);
        }

        return $timeSlots;
    }

    private function teamsCanPlayInTimeSlot(Team $teamA, Team $teamB, array $teamLastTimeSlot, TimeSlot $timeSlot): bool
    {
        $lastSlotTeamA = $teamLastTimeSlot[$teamA->getId()];
        $lastSlotTeamB = $teamLastTimeSlot[$teamB->getId()];

        if ($lastSlotTeamA === null && $lastSlotTeamB === null) {
            return true;
        }

        $minimumInterval = new \DateInterval('PT30M');

        if ($lastSlotTeamA) {
            $lastEndA = clone $lastSlotTeamA->getEnd();
            $lastEndA->add($minimumInterval);
            if ($timeSlot->getStart() < $lastEndA) {
                return false;
            }
        }

        if ($lastSlotTeamB) {
            $lastEndB = clone $lastSlotTeamB->getEnd();
            $lastEndB->add($minimumInterval);
            if ($timeSlot->getStart() < $lastEndB) {
                return false;
            }
        }
        return true;
    }

    #[Route('/versus/edit/{id}', name: 'edit_match', methods: ['GET'])]
    public function edit(Versus $match, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can edit meets.');
        }

//        $timeslots = $entityManager->getRepository(Timeslot::class)->findAll();
//        $timeslots = array_filter($timeslots, function ($timeslot) use ($match) {
//            return $timeslot->getStart() > new \DateTime() && $this->teamsCanPlayInTimeSlot($match->getGreenTeam(), $match->getBlueTeam(), [], $timeslot);
//        });
        return $this->render('versus/edit.html.twig', [
            'match' => $match,
            'teams' => $match->getTeams(),
//            'timeslots' => $timeslots,
        ]);
    }

    #[Route('/versus/update/{id}', name: 'update_match', methods: ['POST'])]
    public function update(Request $request, Versus $match, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can update meets.');
        }

        $greenTeam = $entityManager->getRepository(Team::class)->find($request->request->get('greenTeam'));
        $blueTeam = $entityManager->getRepository(Team::class)->find($request->request->get('blueTeam'));

        //no same team
        if ($greenTeam === $blueTeam) {
            $this->addFlash('error', 'Impossible de faire jouer une équipe contre elle-même.');
            return $this->redirectToRoute('edit_match', ['id' => $match->getId()]);
        }
        $currentDateTime = (new \DateTime())->modify('+1 hour');

        $match->setGreenTeam($greenTeam);
        $match->setBlueTeam($blueTeam);

        $statusRepository = $entityManager->getRepository(Status::class);
        $forfeitStatus = $statusRepository->findOneBy(['name' => 'FORFEITED']);

        $greenTeamForfeit = $request->request->get('greenTeamForfeit');
        $blueTeamForfeit = $request->request->get('blueTeamForfeit');

        $greenTeamMatchStatus = $entityManager->getRepository(TeamMatchStatus::class)->findOneBy([
            'team' => $greenTeam,
            'versus' => $match,
        ]);
        $blueTeamMatchStatus = $entityManager->getRepository(TeamMatchStatus::class)->findOneBy([
            'team' => $blueTeam,
            'versus' => $match,
        ]);

        //if one of the team forfeit
        if ($greenTeamForfeit || $blueTeamForfeit) {
            $match->setGlobalStatus($forfeitStatus);

            if ($greenTeamForfeit) {
                $this->handleTeamForfeit($match, $greenTeam, $blueTeam, $entityManager);
            } else if ($greenTeamMatchStatus) {
                $this->revertTeamForfeit($match, $greenTeam, $entityManager);
                $entityManager->remove($greenTeamMatchStatus);
            }

            if ($blueTeamForfeit) {
                $this->handleTeamForfeit($match, $blueTeam, $greenTeam, $entityManager);
            } else if ($blueTeamMatchStatus) {
                $this->revertTeamForfeit($match, $blueTeam, $entityManager);
                $entityManager->remove($blueTeamMatchStatus);
            }
        } else {
            //no forfeit
            if ($greenTeamMatchStatus) {
                $this->revertTeamForfeit($match, $greenTeam, $entityManager);
                $entityManager->remove($greenTeamMatchStatus);
            }
            if ($blueTeamMatchStatus) {
                $this->revertTeamForfeit($match, $blueTeam, $entityManager);
                $entityManager->remove($blueTeamMatchStatus);
            }

            $status = $request->request->get('status') ?: 'COMING';
            $globalStatus = $statusRepository->findOneBy(['name' => $status]);

            switch ($status) {
                case 'COMING':
                    $match->setBlueScore(null);
                    $match->setGreenScore(null);
                    break;
                case 'IN_PROGRESS':
                    if ($currentDateTime < $match->getTimeslot()->getStart()) {
                        $this->addFlash('error', 'Impossible de mettre un match en cours avant le début du créneau horaire.');
                        return $this->redirectToRoute('edit_match', ['id' => $match->getId()]);
                    }
                    if ($currentDateTime >= $match->getTimeslot()->getEnd()) {
                        $this->addFlash('error', 'Impossible de mettre un match en cours après la fin du créneau horaire.');
                        return $this->redirectToRoute('edit_match', ['id' => $match->getId()]);
                    }
                    $match->setBlueScore(0);
                    $match->setGreenScore(0);
                    break;
                case 'DONE':
                    $greenScore = $request->request->get('greenScore');
                    $blueScore = $request->request->get('blueScore');
                    if (!$greenScore || !$blueScore) {
                        $this->addFlash('error', 'Impossible de terminer un match sans score.');
                        return $this->redirectToRoute('edit_match', ['id' => $match->getId()]);
                    }
                    $match->setGreenScore($greenScore);
                    $match->setBlueScore($blueScore);

                    $this->updateTeamResults($match, $greenTeam, $blueTeam, $entityManager);
                    break;
            }
            if ($globalStatus) {
                $match->setGlobalStatus($globalStatus);
            }
        }
        if ($request->request->has('commentary')) {
            $match->setCommentary($request->request->get('commentary'));
        }
        $entityManager->flush();
        $this->addFlash('success', 'Match mis à jour avec succès.');
        return $this->redirectToRoute('app_match_list');
    }

    private function revertTeamForfeit(Versus $match, Team $team, EntityManagerInterface $entityManager): void
    {
        $teamResults = $team->getTeamResults()->last();

        $teamResults->setLosses($teamResults->getLosses() - 1);
        $teamResults->setPoints($teamResults->getPoints() + 1);

        if ($match->getTimeslot()->getStart() > new \DateTime()) {
            $teamResults->setGamesPlayed($teamResults->getGamesPlayed() - 1);
        }
    }

    private function handleTeamForfeit(Versus $match, Team $forfeitTeam, Team $otherTeam, EntityManagerInterface $entityManager): void
    {
        $teamMatchStatus = $entityManager->getRepository(TeamMatchStatus::class)->findOneBy([
            'team' => $forfeitTeam,
            'versus' => $match,
        ]);

        if (!$teamMatchStatus) {
            $teamMatchStatus = new TeamMatchStatus();
            $teamMatchStatus->setTeam($forfeitTeam)
                ->setVersus($match)
                ->setStatus($entityManager->getRepository(Status::class)->findOneBy(['name' => 'FORFEITED']));

            $entityManager->persist($teamMatchStatus);

            $forfeitTeamResults = $forfeitTeam->getTeamResults()->last();
            $otherTeamResults = $otherTeam->getTeamResults()->last();

            if ($match->getTimeslot()->getStart() > new \DateTime()) {
                $forfeitTeamResults->setGamesPlayed($forfeitTeamResults->getGamesPlayed() + 1);
                $otherTeamResults->setGamesPlayed($otherTeamResults->getGamesPlayed() + 1);
            }

            $forfeitTeamResults->setLosses($forfeitTeamResults->getLosses() + 1);
            $forfeitTeamResults->setPoints($forfeitTeamResults->getPoints() - 1);
        }
    }

    private function updateTeamResults(
        Versus $match,
        Team $greenTeam,
        Team $blueTeam,
        EntityManagerInterface $entityManager
    ): void {
        $greenScore = $match->getGreenScore();
        $blueScore = $match->getBlueScore();

        $greenTeamResults = $greenTeam->getTeamResults()->last();
        $blueTeamResults = $blueTeam->getTeamResults()->last();

        $greenTeamResults->setGamesPlayed($greenTeamResults->getGamesPlayed() + 1);
        $blueTeamResults->setGamesPlayed($blueTeamResults->getGamesPlayed() + 1);

        if ($greenScore > $blueScore) {
            $greenTeamResults->setWins($greenTeamResults->getWins() + 1);
            $greenTeamResults->setPoints($greenTeamResults->getPoints() + 3);
            $blueTeamResults->setLosses($blueTeamResults->getLosses() + 1);
        } elseif ($blueScore > $greenScore) {
            $blueTeamResults->setWins($blueTeamResults->getWins() + 1);
            $blueTeamResults->setPoints($blueTeamResults->getPoints() + 3);
            $greenTeamResults->setLosses($greenTeamResults->getLosses() + 1);
        } else {
            $greenTeamResults->setDraws($greenTeamResults->getDraws() + 1);
            $greenTeamResults->setPoints($greenTeamResults->getPoints() + 1);
            $blueTeamResults->setDraws($blueTeamResults->getDraws() + 1);
            $blueTeamResults->setPoints($blueTeamResults->getPoints() + 1);
        }
    }
}