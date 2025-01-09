<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Team;
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
        $team->addChampionship($championship);
        $championship->addTeam($team);
        $entityManager->flush();
        $this->addFlash('success', 'Votre équipe a rejoint le championnat avec succès');
        return $this->redirectToRoute('app_ranking');
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
