<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Team;
use App\Entity\TeamResults;
use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/championship')]
class ChampionshipController extends AbstractController
{
    #[Route('/', name: 'app_championship')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can view meets.');
        }
        $championships = $entityManager->getRepository(Championship::class)->findAll();

        $finishedChampionships = array_filter($championships, function (Championship $championship) {
            return $championship->getEndDate() < new \DateTime();
        });
        $teams = $entityManager->getRepository(Team::class)->findAll();
        return $this->render('championship/index.html.twig', [
            'controller_name' => 'MeetController',
            'championships' => $championships,
            'finishedChampionships' => $finishedChampionships,
            'teams' => $teams,
        ]);
    }

    #[Route('/import', name: 'app_championship_import', methods: ['POST'])]
    public function import(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can import meets.');
        }

        $file = $request->files->get('file');

        if (!$file) {
            $this->addFlash('error', 'Aucun fichier sélectionné.');
            return $this->redirectToRoute('app_championship');
        }

        $jsonData = file_get_contents($file->getPathname());
        $championships = json_decode($jsonData, true);

        if (!$championships) {
            $this->addFlash('error', 'Format JSON invalide, vérifiez le fichier.');
            return $this->redirectToRoute('app_championship');
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
        return $this->redirectToRoute('app_championship');
    }

    #[Route('/create', name: 'app_championship_create', methods: ['POST'])]
    function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can create meets.');
        }
        $name = $request->request->get('name');
        $date_start = new \DateTime($request->request->get('date_start'));
        $date_end = new \DateTime($request->request->get('date_end'));
        if (empty($name)) {
            $this->addFlash('error', 'Le nom du championnat est obligatoire.');
            return $this->redirectToRoute('app_championship');
        }
        $championships = $entityManager->getRepository(Championship::class)->findAll();
        if ($championships) {
            foreach ($championships as $chp) {
                if ($chp->getStartDate() < $date_start && $chp->getEndDate() > $date_end) {
                    $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                    return $this->redirectToRoute('app_championship');
                }
                if ($chp->getStartDate() < $date_end && $chp->getEndDate() > $date_end) {
                    $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                    return $this->redirectToRoute('app_championship');
                }
                if ($chp->getStartDate() >= $date_start && $chp->getStartDate() <= $date_end) {
                    $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                    return $this->redirectToRoute('app_championship');
                }
                if ($chp->getEndDate() >= $date_start && $chp->getEndDate() <= $date_end) {
                    $this->addFlash('error', 'Un championnat est déjà en cours sur ces dates.');
                    return $this->redirectToRoute('app_championship');
                }
            }
        }
        if ($date_start >= $date_end) {
            $this->addFlash('error', 'Les dates ne sont pas cohérentes.');
            return $this->redirectToRoute('app_championship');
        }
        $championship = new Championship();
        $championship->setName($name);
        $championship->setStartDate($date_start);
        $championship->setEndDate($date_end);
        $championship->setOrganizer($this->getUser());
        $entityManager->persist($championship);
        $this->addFlash('success', 'Championnat créé avec succès.');
        $logger->debug('Persisting changes.');
        $entityManager->flush();
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/{id}/join', name: 'join_championship', methods: ['POST'])]
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

    #[Route('/{id}/edit', name: 'app_champ_edit', methods: ['GET'])]
    public function edit(Championship $championship, EntityManagerInterface $entityManager, Request $request): Response
    {
        $fields = $championship->getFields();

        $selectedChampionshipId = $request->query->get('select');

        return $this->render('championship/edit.html.twig', [
            'championship' => $championship,
            'fields' => $fields,
            'select' => $selectedChampionshipId,

        ]);
    }

    #[Route('/{id}/apply-changes', name: 'app_championship_edit', methods: ['POST'])]
    public function champedit(Request $request, Championship $championship, EntityManagerInterface $entityManager): Response
    {
        $newName = $request->request->get('firstName');
        if ($newName) {
            $championship->setName($newName);
        }
        $fieldIdToRemove = $request->request->get('terrain');
        $fieldToRemove = null;
        foreach ($championship->getFields() as $field) {
            if ($field->getId() == $fieldIdToRemove) {
                $fieldToRemove = $field;
                break;
            }
        }
        if ($fieldToRemove) {
            $championship->removeField($fieldToRemove);
            $this->addFlash('success', 'Le terrain a été supprimé avec succès');
        }
        $entityManager->remove($fieldToRemove);
        $entityManager->persist($championship);
        $entityManager->flush();
        return $this->redirectToRoute('app_champ_edit', [
            'id' => $championship->getId()
        ]);
    }

    #[Route('/{id}/leave', name: 'leave_championship', methods: ['POST'])]
    public function leaveChampionship(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('leave' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        $team = $this->getUser()->getMember()->getTeam();
        if (!$team) {
            throw $this->createAccessDeniedException('Vous devez faire partie d\'une équipe');
        }
        $championship = $entityManager->getRepository(Championship::class)->find($id);
        if (!$championship) {
            throw $this->createNotFoundException('Championnat non trouvé.');
        }
        $team->removeChampionship($championship);
        $championship->removeTeam($team);
        $entityManager->flush();
        $this->addFlash('success', 'Votre équipe a quitté le championnat avec succès');
        return $this->redirectToRoute('app_ranking');
    }
}
