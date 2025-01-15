<?php

// src/Controller/RankingController.php
namespace App\Controller;

use App\Repository\TeamRepository;
use App\Repository\ChampionshipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends AbstractController
{
    #[Route('/ranking', name: 'app_ranking')]
    public function index(
        Request $request,
        ChampionshipRepository $championshipRepository,
        TeamRepository $teamRepository
    ): Response {
        $championships = $championshipRepository->findAll();
        $selectedChampionshipId = $request->query->get('championship');
        $teams = [];

        if (!$selectedChampionshipId && count($championships) > 0) {
            $selectedChampionshipId = $championships[0]->getId();
        }

        if ($selectedChampionshipId) {
            $teams = $teamRepository->findTeamsByChampionship((int)$selectedChampionshipId);
        }

        return $this->render('ranking/index.html.twig', [
            'championships' => $championships,
            'teams' => $teams,
            'selectedChampionshipId' => $selectedChampionshipId
        ]);
    }
}
