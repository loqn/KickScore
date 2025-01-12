<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TournamentType;
use App\Entity\BinaryVersus;
use App\Entity\TeamResults;
use App\Entity\Tournament;
use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TournamentRepository;

class TournamentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Tournament $tournament;

    #[Route('/tournament', name: 'app_tournament')]
    public function tournamentList(Request $request, EntityManagerInterface $entityManager, TournamentRepository $tournamentRepository): Response
    {
        $this->entityManager = $entityManager;
        $form = $this->createForm(TournamentType::class);
        $matches = null;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tournamentId = $request->get('id');
            $this->tournament = $tournamentRepository->find($tournamentId);

            if (!$this->tournament) {
                throw $this->createNotFoundException('Tournoi introuvable.');
            }
    
            $this->tournament->setFinal(new BinaryVersus);
            $this->saveMatch($this->tournament->getFinal());
            $teams = $this->getBestTeams(16);
            $matches = $this->makeMatches($teams);
            $matches = $this->setTournament(3, $this->tournament->getFinal(), $matches);
        }

        return $this->render('tournament/index.html.twig', [
            'form' => $form->createView(),
            'matches' => $matches,
        ]);
    }

    public function refreshTournament(BinaryVersus $match): ?BinaryVersus
    {
        $match1 = $match->getPrevious()[0]->getMatch();
        $match2 = $match->getPrevious()[1]->getMatch();

        if ($match1 == null) return $this->refreshTournament($match->getPrevious()[0]); 
        if ($match2 == null) return $this->refreshTournament($match->getPrevious()[1]);
        
        $scoreB1 = $match1->getBlueScore();
        $scoreG1 = $match1->getGreenScore();
        $scoreB2 = $match2->getBlueScore();
        $scoreG2 = $match2->getGreenScore();

        if ($scoreB1 == null or $scoreG1 == null) return null;

        if ($scoreB1 < $scoreG1) $match->setGreenTeam($match1->getGreenTeam());
        if ($scoreB1 > $scoreG1) $match->setBlueTeam($match1->getBlueTeam());
        if ($scoreB2 < $scoreG2) $match->setGreenTeam($match2->getGreenTeam());
        if ($scoreB2 > $scoreG2) $match->setBlueTeam($match2->getBlueTeam());

        return $match;
    }

    private function saveMatch(Versus $match): void
    {
        $match->setGreenScore(0);
        $match->setBlueScore(0);
        $match->setDate($this->tournament->getChampionship()->getStartDate());
        $match->setCommentary("");
        $match->setChampionship($this->tournament->getChampionship());
        $this->entityManager->persist($match);
        $this->entityManager->flush(); 
    }

    private function makeMatches(array $teams): array
    {
        $match = new Versus;

        $randomKey = array_rand($teams);
        $match->setBlueTeam($teams[$randomKey]);
        unset($teams[$randomKey]);

        $randomKey = array_rand($teams);
        $match->setGreenTeam($teams[$randomKey]);
        unset($teams[$randomKey]);

        $this->saveMatch($match);

        if (count($teams) == 0) return [$match];
        return $this->makeMatches($teams) + [$match];
    }

    private function getBestTeams(int $number): array
    {
        $teams = [];
        $teamsResultsFiltered = [];
        $teamsResults = $this->tournament->getChampionship()->getTeamResults();
        foreach ($teamsResults as $result) {
            if (count($teams) < $number &&
                $this->canAccessTournament($result, $teamsResultsFiltered)
            ) {
                array_push($teamsResultsFiltered, $result);
                array_push($teams, $result->getTeam());
            }
            $result->getTeam();
        }
        return $teams;
    }

    private function canAccessTournament(TeamResults $result, array $teamsResultsFiltered): bool
    {
        foreach ($teamsResultsFiltered as $team) {
            if ($team->getPoints() <= $result->getPoints()) {
                return true;
            }
        }
        return false;
    }

    private function setTournament(int $depth, BinaryVersus $match, array $matches): BinaryVersus
    {
        if ($depth <= 2) {
            $match->setPrevious([array_pop($matches), array_pop($matches)]);
            return $match;
        }
        $previousDuel = [new BinaryVersus(), new BinaryVersus()];
        $match->setPrevious($previousDuel);
        [$previousDuel1, $previousDuel2] = $previousDuel;
        $this->saveMatch($previousDuel1);
        $this->saveMatch($previousDuel2);
        $this->setTournament($depth-1, $previousDuel1, $matches);
        $this->setTournament($depth-1, $previousDuel2, $matches);
        return $match;
    }
}