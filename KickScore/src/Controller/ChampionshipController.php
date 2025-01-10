<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\TeamResults;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChampionshipController extends AbstractController
{
    #[Route('/join_championship/{id}', name: 'join_championship', methods: ['POST'])]
    public function joinChampionship(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('join' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }
        $team = $this->getUser()->getMember()->getTeam();
        if (!$team) {
            throw $this->createAccessDeniedException('Vous devez faire partie d\'une équipe');
        }
        $championship = $entityManager->getRepository(Championship::class)->find($id);
        if (!$championship) {
            throw $this->createNotFoundException('Championnat non trouvé');
        }
        $teamResults = new TeamResults();
        $teamResults->setTeam($team);
        $teamResults->setChampionship($championship);
        $entityManager->persist($teamResults);
        $team->addChampionship($championship);
        $championship->addTeam($team);
        $entityManager->flush();
        $this->addFlash('success', 'Votre équipe a rejoint le championnat avec succès');
        return $this->redirectToRoute('app_team_edit', ['id' => $team->getId()]);
    }

    #[Route('/{id}/champedit', name: 'app_champ_edit', methods: ['GET'])]
    public function edit(Championship $championship, EntityManagerInterface $entityManager, Request $request): Response
    {
        // get the fields of the championship
        $fields = $championship->getFields();

        $selectedChampionshipId = $request->query->get('select');

        return $this->render('Championship/edit.html.twig', [
            'championship' => $championship,
            'fields' => $fields,
            'select' => $selectedChampionshipId,

        ]);
    }

    #[Route('/championshipedit', name: 'app_championship_edit', methods: ['POST'])]
    public function champedit(Request $request, Championship $championship, EntityManagerInterface $entityManager): Response
    {
        return $this->render('Championship/edit.html.twig', [
            'championship' => $championship
        ]);
    }

    #[Route('/leave_championship/{id}', name: 'leave_championship', methods: ['POST'])]
    public function leaveChampionship(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('leave' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }
        $team = $this->getUser()->getMember()->getTeam();
        if (!$team) {
            throw $this->createAccessDeniedException('Vous devez faire partie d\'une équipe');
        }
        $championship = $entityManager->getRepository(Championship::class)->find($id);
        if (!$championship) {
            throw $this->createNotFoundException('Championnat non trouvé');
        }
        $team->removeChampionship($championship);
        $championship->removeTeam($team);
        $entityManager->flush();
        $this->addFlash('success', 'Votre équipe a quitté le championnat avec succès');
        return $this->redirectToRoute('app_ranking');
    }
}
