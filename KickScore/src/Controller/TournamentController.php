<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Tournament;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('/tournament/{id}', name: 'tournament', methods: ['GET'])]
    public function show(Tournament $tournament): Response
    {
        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route('/tournament/{id}/edit', name: 'tournament_edit', methods: ['GET'])]
    public function edit(Tournament $tournament): Response
    {
        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route('/tournament/generate/{id}', name: 'app_generate_tournament', methods: ['POST'])]
    public function generate(Championship $championship): Response
    {
        $tournament = new Tournament();
        $tournament->setChampionship($championship);
        $championship->setTournament($tournament);

        $this->addFlash('success', 'Le tournoi a été généré avec succès');
        return $this->redirectToRoute('app_user_index');
    }
}