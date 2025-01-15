<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TournamentType;
use App\Entity\BinaryVersus;
use App\Entity\PlayoffMatch;
use App\Entity\TeamResults;
use App\Entity\Tournament;
use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TournamentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $matches = null;
        $this->tournament = new Tournament;
        $form->handleRequest($request);
        $this->arrayTournament = array_fill(0, 4, []);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tournamentId = $data['tournament'];
            $this->tournament = $tournamentRepository->find($tournamentId);
            
            if (!$this->tournament) {
                throw $this->createNotFoundException('Tournoi introuvable.');
            }
    
            $actionType = $form->get('action_type')->getData();
            
            if ($actionType === 'refresh') {
                // Logique de rafraîchissement ici
            } else {
                $final = $this->tournament->getFinal();
                
                if ($final === null) {
                    $finalMatch = new PlayoffMatch();
                    $this->tournament->setFinal($finalMatch);
                    
                    $teams = $this->getBestTeams(16);
                    $matches = $this->makeMatches($teams);
                    $this->tournament->setFinal($this->setTournament(3, $finalMatch, $matches));
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


        return $this->render('tournament/index.html.twig', [
            'form' => $form->createView(),
            'matches' => $this->arrayTournament,
            'tournament' => $this->tournament,
        ]);
    }

    #[Route('/tournament/refresh', name: 'app_tournament_refresh', methods: ['POST'])]
    public function refreshTournamentAction(Request $request, EntityManagerInterface $entityManager, TournamentRepository $tournamentRepository): Response
    {
        $this->entityManager = $entityManager;
    
        $tournamentId = $request->get('tournament_id');
        $this->tournament = $tournamentRepository->find($tournamentId);
        
        if (!$this->tournament) {
            throw $this->createNotFoundException('Tournoi introuvable.');
        }
    
        if ($this->tournament->getFinal() !== null) { 
            $this->refreshTournament($this->tournament->getFinal());
            $this->tournamentInArray(3,$this->tournament->getFinal());
            $this->entityManager->flush();
        }
    
        return $this->redirectToRoute('app_tournament', [
            'tournament_id' => $tournamentId,
            'matches' => $this->arrayTournament,
            'tournament' => $this->tournament,
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

        if ($match1->getPreviousMatchOne() == null or $match1->getPreviousMatchTwo() == null){

            return $this->refreshTournament($match->getPreviousMatchOne()); 
        } 
        if ($match2->getPreviousMatchOne() == null or $match2->getPreviousMatchTwo() == null){
            return $this->refreshTournament($match->getPreviousMatchTwo());
        } 
        
        $scoreB1 = $match1->getBlueTeamScore();
        $scoreG1 = $match1->getGreenTeamScore();
        $scoreB2 = $match2->getBlueTeamScore();
        $scoreG2 = $match2->getGreenTeamScore();

        if ($scoreB1 < $scoreG1) $match->setGreenTeam($match1->getGreenTeam());
        if ($scoreB1 > $scoreG1) $match->setBlueTeam($match1->getBlueTeam());
        if ($scoreB2 < $scoreG2) $match->setGreenTeam($match2->getGreenTeam());
        if ($scoreB2 > $scoreG2) $match->setBlueTeam($match2->getBlueTeam());

        return $match;
    }

    private function saveMatch(PlayoffMatch $match): void
    {
        
        $match->setGreenTeamScore(0);
        $match->setBlueTeamScore(0);
        $match->setDate($this->tournament->getChampionship()->getStartDate());
        $match->setCommentary("");
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

    private function setTournament(int $depth, PlayoffMatch $match, array &$matches): PlayoffMatch
    {
           // Cas de base : on est au niveau des feuilles
    if ($depth < 2) {
        $matchOne = array_pop($matches);
        $matchTwo = array_pop($matches);
        
        if ($matchOne && $matchTwo) {
            $match->setPreviousMatchOne($matchOne);
            $match->setPreviousMatchTwo($matchTwo);
            
            // Persister dans l'ordre : enfants puis parent
            $this->entityManager->persist($matchOne);
            $this->entityManager->persist($matchTwo);
            $this->entityManager->persist($match);
        }
        
        return $match;
    }
    
        // Création des matches enfants
        $matchOne = new PlayoffMatch();
        $matchTwo = new PlayoffMatch();
        
        // Établir les relations
        $match->setPreviousMatchOne($matchOne);
        $match->setPreviousMatchTwo($matchTwo);
        
        // Appels récursifs
        $this->setTournament($depth - 1, $matchOne, $matches);
        $this->setTournament($depth - 1, $matchTwo, $matches);
        
        // Persister dans l'ordre : enfants puis parent
        $this->entityManager->persist($matchOne);
        $this->entityManager->persist($matchTwo);
        $this->entityManager->persist($match);
    
    return $match;
    }
    
private function saveFullTournament(PlayoffMatch $finalMatch): void
{
    $this->entityManager->persist($finalMatch);
    $this->entityManager->flush();
}
        
}