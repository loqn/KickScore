<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TournamentType;
use App\Entity\PlayoffMatch;
use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TournamentRepository;
use App\Repository\PlayoffMatchRepository;

class TournamentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Tournament $tournament;
    private $arrayTournament;

    #[Route('/tournament', name: 'app_tournament', methods: ['GET', 'POST'])]
    public function tournamentList(Request $request, EntityManagerInterface $entityManager, TournamentRepository $tournamentRepository): Response
    {
        $form = $this->createForm(TournamentType::class);
        $form->get('action_type')->setData('default');
        $this->entityManager = $entityManager;
        $this->tournament = new Tournament;
        $this->arrayTournament = array_fill(0, 4, []);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tournamentId = $data['tournament'];
            $this->tournament = $tournamentRepository->find($tournamentId);
    
            if (!$this->tournament) {
                throw $this->createNotFoundException('Tournoi introuvable.');
            }
    
            $actionType = $form->get('action_type')->getData();
            if ($actionType === 'refresh') {
                if ($this->tournament->getFinal()) {
                    $this->tournamentInArray(3, $this->tournament->getFinal());
                }
            } else {
                $final = $this->tournament->getFinal();
                if ($final === null) {
                    $finalMatch = new PlayoffMatch();
                    $finalMatch->setTournament($this->tournament);
                    $this->tournament->setFinal($finalMatch);
                    
                    $teams = $this->getBestTeams(16);
                    $matches = $this->makeMatches($teams);
                    $this->tournament->setFinal($this->setupTournament(3, $finalMatch, $matches));
                    $this->saveFullTournament($finalMatch);
                    $this->arrayTournament[0] = $matches;
                    
                    if ($this->tournament->getFinal() !== null) {
                        $this->tournamentInArray(3, $this->tournament->getFinal());
                    }
                    
                    $this->entityManager->persist($this->tournament);
                    $this->entityManager->flush();
                } else {
                    if ($this->tournament->getFinal() !== null) {
                        $this->tournamentInArray(3, $this->tournament->getFinal());
                    }
                }
            }
        }
        else if ($request->get('tournament_id')) {
            $this->tournament = $tournamentRepository->find($request->get('tournament_id'));
            
            if ($this->tournament && $this->tournament->getFinal()) {
                $this->tournamentInArray(3, $this->tournament->getFinal());
            }
        }
    
        return $this->render('tournament/index.html.twig', [
            'form' => $form->createView(),
            'matches' => $this->arrayTournament,
            'tournament' => $this->tournament,
        ]);
    }
    

    #[Route('/tournament/refresh', name: 'app_tournament_refresh', methods: ['POST'])]
    #[Route('/tournament/refresh', name: 'app_tournament_refresh', methods: ['POST'])]
    public function refreshTournamentAction(Request $request, EntityManagerInterface $entityManager, TournamentRepository $tournamentRepository): Response
    {
        $this->entityManager = $entityManager;
        $this->arrayTournament = array_fill(0, 4, []); 
        
        $tournamentId = $request->get('tournament_id');
        $this->tournament = $tournamentRepository->find($tournamentId);
        
        if (!$this->tournament) {
            throw $this->createNotFoundException('Tournoi introuvable.');
        }

        if ($this->tournament->getFinal() !== null) { 
            $this->refreshTournament($this->tournament->getFinal());
            $this->tournamentInArray(3, $this->tournament->getFinal());
            $this->entityManager->flush();
        }

        $form = $this->createForm(TournamentType::class);
        $form->get('action_type')->setData('default');

        return $this->render('tournament/index.html.twig', [
            'form' => $form->createView(),
            'matches' => $this->arrayTournament,
            'tournament' => $this->tournament
        ]);
    }

    public function tournamentInArray(int $depth, PlayoffMatch $match){
        array_push($this->arrayTournament[$depth], $match);
        if($depth < 1) return;
        $this->tournamentInArray($depth-1, $match->getPreviousMatchOne());
        $this->tournamentInArray($depth-1, $match->getPreviousMatchTwo());
    }

    public function refreshTournament(PlayoffMatch $match): ?PlayoffMatch
    {
        $match1 = $match->getPreviousMatchOne();
        $match2 = $match->getPreviousMatchTwo();
    
        if ($match1 && ($match1->getPreviousMatchOne() !== null || $match1->getPreviousMatchTwo() !== null)) {
            $this->refreshTournament($match1);
        }
    
        if ($match2 && ($match2->getPreviousMatchOne() !== null || $match2->getPreviousMatchTwo() !== null)) {
            $this->refreshTournament($match2);
        }
    
        $scoreB1 = $match1 ? $match1->getBlueTeamScore() : 0;
        $scoreG1 = $match1 ? $match1->getGreenTeamScore() : 0;
        $scoreB2 = $match2 ? $match2->getBlueTeamScore() : 0;
        $scoreG2 = $match2 ? $match2->getGreenTeamScore() : 0;

        if ($scoreB1 < $scoreG1) $match->setGreenTeam($match1->getGreenTeam());
        if ($scoreB1 > $scoreG1) $match->setGreenTeam($match1->getBlueTeam());
        if ($scoreB2 < $scoreG2) $match->setBlueTeam($match2->getGreenTeam());
        if ($scoreB2 > $scoreG2) $match->setBlueTeam($match2->getBlueTeam());

        $this->entityManager->persist($match);

        return $match;
    }

    private function saveMatch(PlayoffMatch $match): void
    {
        
        $match->setGreenTeamScore(0);
        $match->setBlueTeamScore(0);
        $match->setDate($this->tournament->getChampionship()->getStartDate());
        $match->setCommentary("");
        $match->setTournament($this->tournament); 
        $this->entityManager->persist($match);
        $this->entityManager->flush(); 
    }

    private function makeMatches(array $teams): array
    {      
        $match = new PlayoffMatch;

        $randomKey = array_rand($teams);
        $match->setBlueTeam($teams[$randomKey]);
        unset($teams[$randomKey]);

        $randomKey = array_rand($teams);
        $match->setGreenTeam($teams[$randomKey]);
        unset($teams[$randomKey]);

        $this->saveMatch($match);

        if (count($teams) == 0) return [$match];
        return array_merge($this->makeMatches($teams), [$match]);
    }

    private function getBestTeams(int $number): array
    {
        $teams = [];
        $teamsResults = $this->tournament->getChampionship()->getTeams();
        $sortedTeamResults = $teamsResults->toArray();
        usort($sortedTeamResults, function($a, $b) {
        return $b->getPoints() - $a->getPoints();
        });
        for ($i = 0; $i < $number && $i < count($sortedTeamResults); $i++) {
            $teams[] = $sortedTeamResults[$i];
        }
        return $teams;
    }

    private function setupTournament(int $depth, PlayoffMatch $match, array &$matches): PlayoffMatch
    {

        $match->setTournament($this->tournament);
        if ($depth < 2) {
            $matchOne = array_pop($matches);
            $matchTwo = array_pop($matches);
            
            if ($matchOne && $matchTwo) {
                $match->setPreviousMatchOne($matchOne);
                $match->setPreviousMatchTwo($matchTwo);
                
                $this->entityManager->persist($matchOne);
                $this->entityManager->persist($matchTwo);
                $this->entityManager->persist($match);
            }
            
            return $match;
        }
        
            $matchOne = new PlayoffMatch();
            $matchTwo = new PlayoffMatch();

            $matchOne->setTournament($this->tournament);
            $matchTwo->setTournament($this->tournament); 

            $match->setPreviousMatchOne($matchOne);
            $match->setPreviousMatchTwo($matchTwo);
            
            $this->setupTournament($depth - 1, $matchOne, $matches);
            $this->setupTournament($depth - 1, $matchTwo, $matches);
            
            $this->entityManager->persist($matchOne);
            $this->entityManager->persist($matchTwo);
            $this->entityManager->persist($match);
        
        return $match;
    }
    
    private function saveFullTournament(PlayoffMatch $finalMatch): void
    {
        $finalMatch->setTournament($this->tournament);
        $this->entityManager->persist($finalMatch);

        $this->entityManager->persist($this->tournament);
        $this->entityManager->flush();
    }
            


    #[Route('/match/{id}/update-score', name: 'app_update_match_score', methods: ['POST'])]
    public function updateMatchScore(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        PlayoffMatchRepository $matchRepository
    ): Response {
        $this->entityManager = $entityManager;
    
        $match = $matchRepository->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match introuvable.');
        }
    
        $blueTeamScore = (int) $request->request->get('blue_team_score');
        $greenTeamScore = (int) $request->request->get('green_team_score');
    
        $match->setBlueTeamScore($blueTeamScore);
        $match->setGreenTeamScore($greenTeamScore);
    
        $entityManager->persist($match);
        $entityManager->flush();
    
        $tournament = $match->getTournament();
        if (!$tournament) {
            throw $this->createNotFoundException('Tournoi introuvable pour ce match.');
        }

        return $this->redirectToRoute('app_tournament', [
            'tournament_id' => $tournament->getId() 
        ]);
    }
}