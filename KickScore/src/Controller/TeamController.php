<?php

namespace App\Controller;
use App\Entity\Championship;
use App\Entity\Member;
use App\Entity\Team;
use App\Entity\TeamResults;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/team')]
class TeamController extends AbstractController
{
    #[Route('/', name: 'app_team')]
    public function index(EntityManagerInterface $entityManager,Request $request): Response
    {
        $players = $entityManager->getRepository(User::class)->findAll();

        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour créer une équipe.');
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ORGANIZER')) {
            $this->addFlash('error', 'Organizers can\'t create teams.');
            return $this->redirectToRoute('app_root');
        }

        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'players' => $players
        ]);
    }

    #[Route('/create', name: 'app_team_create', methods: ['POST', 'GET'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if($request->getMethod() !== 'POST'){
            $this->addFlash('error', 'Erreur : Vous ne pouvez pas accéder à cette page.');
            return $this->redirectToRoute('app_error');
        }

        if ($this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException("organizers can't create teams.");
        }
        if ($entityManager->getRepository(Team::class)->findOneBy(['name' => $request->request->get('name')])) {
            $this->addFlash('error', 'Erreur : une équipe possède déjà ce nom.');
            return $this->redirectToRoute('app_team');
        }
        $name = $request->request->get('name');
        $structure = $request->request->get('structure');
        if (empty($name)) {
            $this->addFlash('error', 'Erreur : le nom de l\'équipe ne peut pas être vide.');
            return $this->redirectToRoute('app_team');
        }
        $team = new Team();
        $team->setName($name);
        $team->setStructure($structure);
        $team->setWin(0);
        $team->setDraw(0);
        $team->setLose(0);
        $points = 0;
        $gameplayed = 0;
        $team->setPoints($points);
        $team->setGamePlayed($gameplayed);

        if ($request->request->get('userJoin')) {
            $member = new Member();
            $member->setUser($this->getUser());
            $member->setTeam($team);
            $member->setFname($this->getUser()->getFirstName());
            $member->setName($this->getUser()->getName());
            $member->setEmail($this->getUser()->getMail());
            $this->getUser()->setMember($member);
            $this->getUser()->getMember()->setTeam($team);
            $team->addMember($member);
        }

        $entityManager->persist($team);
        $entityManager->flush();
        $this->addFlash('success', 'L\'équipe a été créée avec succès.');
        if($request->request->get('userJoin')){
            return $this->redirectToRoute('app_team_edit', ['id' => $team->getId()]);
        }
        return $this->redirectToRoute('app_team');
    }

    #[Route('/edit/add_member', name: 'app_team_add_member', methods: ['POST'])]
    public function addMember(EntityManagerInterface $entityManager, Request $request, LoggerInterface $logger): Response
    {
        if ($this->isGranted('ROLE_ORGANIZER')) {
            $teamId = $request->request->get('team_id');
            $team = $entityManager->getRepository(Team::class)->find($teamId);

            if (!$team) {
                throw $this->createNotFoundException('L\'équipe renseignée n\'a pas été trouvée');
            }
            $firstMember = $team->getMembers()->first();
            $teamCreator = $firstMember->getUser();
            if (!$teamCreator) {
                throw $this->createNotFoundException('Le créateur de l\'équipe n\'a pas été trouvé');
            }
            $user = $teamCreator;
        } else {
            $user = $this->getUser();
            if (!$user) {
                $logger->alert('User not logged in');
                throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter des membres.');
            }
            $team = $user->getMember()->getTeam();
            if (!$team) {
                $logger->alert('User has no team');
                throw $this->createAccessDeniedException('Vous ne faites partie d\'aucune équipe.');
            }
        }

        $jsonData = $request->request->get('to_add');
        $membersData = json_decode($jsonData, true);

        if (!is_array($membersData)) {
            $membersData = [$membersData];
        }

        $addedCount = 0;

        foreach ($membersData as $memberData) {
            if (!isset($memberData['email'], $memberData['fname'], $memberData['name'])) {
                continue;
            }

            $existingMember = $entityManager->getRepository(Member::class)->findOneBy([
                'email' => $memberData['email']
            ]);

            if (!$existingMember) {
                $member = new Member();
                $member->setTeam($team)
                    ->setFname($memberData['fname'])
                    ->setName($memberData['name'])
                    ->setEmail($memberData['email']);

                $team->addMember($member);
                $entityManager->persist($member);
                $addedCount++;
            }
        }

        $entityManager->flush();

        if ($addedCount > 0) {
            $this->addFlash(
                'success',
                $addedCount === 1
                    ? 'Le membre a été ajouté avec succès.'
                    : $addedCount . ' membres ajoutés avec succès.'
            );
        } else {
            $this->addFlash('info', 'Aucun membre ajouté.');
        }

        return $this->redirectToRoute('app_team_edit', [
            'id' => $team->getId()
        ]);
    }

    #[Route('/edit/{id}', name: 'app_team_edit')]
    public function edit(Team $team, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            $userTeam = $this->getUser()->getMember()?->getTeam();
            if ($userTeam !== $team) {
                throw $this->createAccessDeniedException('You can only edit your own team.');
            }
        }

        $championships = $team->getChampionships();
        $teamResults = [];

        foreach ($championships as $championship) {
            $results = $entityManager->getRepository(TeamResults::class)->findOneBy([
                'team' => $team,
                'championship' => $championship
            ]);

            if ($results) {
                $teamResults[$championship->getId()] = [
                    'wins' => $results->getWins(),
                    'losses' => $results->getLosses(),
                    'draws' => $results->getDraws(),
                    'points' => $results->getPoints()
                ];
            }
        }

        return $this->render('team/edit.html.twig', [
            'controller_name' => 'TeamController',
            'team' => $team,
            'championships' => $championships,
            'teamResults' => $teamResults
        ]);
    }

    #[Route('/edit/remove_member/{id}', name: 'app_team_remove_member', methods: ['POST'])]
    public function removeMember(Member $member, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('You can only remove members from your own team.');
        }
        $user = $member->getUser();
        $user->getMember()->setTeam(null);
        $user->removeMember();
        $entityManager->persist($user);
        $entityManager->remove($member);
        $entityManager->flush();
        $this->addFlash('success', 'Member removed successfully');
        return $this->redirectToRoute('app_team_edit', ['id' => $member->getTeam()->getId()]);
    }
    #[Route('/teams/ranking', name: 'app_teams_ranking')]
    public function ranking(TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findAllTeamsByPoints();
        
        return $this->render('team/ranking.html.twig', [
            'teams' => $teams
        ]);
    }
}
