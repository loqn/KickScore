<?php

namespace App\Controller;
use App\Entity\Member;
use App\Entity\Team;
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

        if ($this->isGranted('ROLE_ORGANIZER')) {
            $this->addFlash('error', 'Organizers can\'t create teams.');
            return $this->redirectToRoute('app_root');
        }

        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'players' => $players
        ]);
    }

    #[Route('/create', name: 'app_team_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException("organizers can't create teams.");
        }
        $name = $request->request->get('name');
        $structure = $request->request->get('structure');
        $win = $request->request->get('win');
        $draw = $request->request->get('draw');
        $lose = $request->request->get('lose');

        if (empty($name)) {
            $this->addFlash('error', 'Name is required.');
            return $this->redirectToRoute('app_team');
        }
        $team = new Team();
        $team->setName($name);
        $team->setStructure($structure);
        $team->setWin($win);
        $team->setDraw($draw);
        $team->setLose($lose);
        $points = 3*$win + $draw;
        $gameplayed = $win + $draw + $lose;
        $team->setPoints($points);
        $team->setGamePlayed($gameplayed);

        if ($request->request->get('userJoin')) {
            $member = new Member();
            $member->setUser($this->getUser());
            $member->setTeam($team);
            $member->setFname($this->getUser()->getFirstName());
            $member->setName($this->getUser()->getName());
            $member->setEmail($this->getUser()->getMail());
            $this->getUser()->setTeam($team);
            $team->addMember($member);
        }

        $entityManager->persist($team);
        $entityManager->flush();
        $this->addFlash('success', 'team added successfully');
        return $this->redirectToRoute('app_team_edit', ['id' => $team->getId()]);
    }

    #[Route('/edit/add_member', name: 'app_team_add_member', methods: ['POST'])]
    public function addMember(EntityManagerInterface $entityManager, Request $request, LoggerInterface $logger): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('User not found');
        }
        $team = $user->getTeam();
        if (!$team) {
            $logger->alert('User has no team');
            throw $this->createAccessDeniedException('You don\'t have a team');
        }
        $userData = json_decode($request->request->get('to_add'), true);
        $to_add = new ArrayCollection([$userData]);
        foreach($to_add as $temp_mbr) {
            $temp_email = $temp_mbr['email'];
            if (!$entityManager->getRepository(Member::class)->findOneBy(['email' => $temp_email])) {
                $member = new Member();
                $member->setTeam($team);
                $member->setFname($temp_mbr['fname']);
                $member->setName($temp_mbr['name']);
                $member->setEmail($temp_email);
                $team->addMember($member);
                $entityManager->persist($member);
            }
        }
        $entityManager->flush();
        $this->addFlash('success', $to_add->count().'member(s) were added successfully');
        return $this->redirectToRoute('app_team_edit', ['id' => $this->getUser()->getTeam()->getId()]);
    }

    #[Route('/edit/{id}', name: 'app_team_edit')]
    public function edit(Team $team, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->getUser()->getTeam() !== $team && !$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('You can only edit your own team.');
        }
        return $this->render('team/edit.html.twig', [
            'controller_name' => 'TeamController',
            'team' => $team
        ]);
    }

    #[Route('/edit/remove_member/{id}', name: 'app_team_remove_member', methods: ['POST'])]
    public function removeMember(Member $member, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('You can only remove members from your own team.');
        }
        $user = $member->getUser();
        $user->setTeam(null);
        $entityManager->persist($user);
        $entityManager->remove($member);
        $entityManager->flush();
        $this->addFlash('success', 'Member removed successfully');
    }
}
