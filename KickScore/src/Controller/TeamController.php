<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Member;
use App\Entity\Team;
use App\Entity\TeamResults;
use App\Entity\User;
use App\Entity\Versus;
use App\Repository\TeamRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
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
    public function index(EntityManagerInterface $entityManager, Request $request, TranslatorInterface $translator): Response
    {
        $players = $entityManager->getRepository(User::class)->findAll();

        if (!$this->getUser()) {
            $this->addFlash('error',
            $translator->trans('flash.mustconnected'));
            return $this->redirectToRoute('app_login');
        }

        if($this->getUser()->getMember()) {
            $this->addFlash('error', 'Vous êtes déjà membre d\'une équipe.');
            return $this->redirectToRoute('app_team_edit', ['id' => $this->getUser()->getMember()->getTeam()->getId()]);
        }

        if ($this->isGranted('ROLE_ORGANIZER')) {
            $this->addFlash('error', $translator->trans('flash.organizer_no_team'));
            return $this->redirectToRoute('app_error');
        }

        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'players' => $players
        ]);
    }

    #[Route('/create', name: 'app_team_create', methods: ['POST', 'GET'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        if ($request->getMethod() !== 'POST') {
            $this->addFlash('error', $translator->trans('flash.error.invalid_access'));
            return $this->redirectToRoute('app_error');
        }

        if (!$this->getUser()) {
            $this->addFlash('error', $translator->trans('flash.error.must_login_team'));
            return $this->redirectToRoute('app_login');
        }

        if($this->getUser()->getMember()) {
            $this->addFlash('error', $translator->trans('flash.error.already_team_member'));
            return $this->redirectToRoute('app_team_edit', ['id' => $this->getUser()->getMember()->getTeam()->getId()]);
        }

        if ($this->isGranted('ROLE_ORGANIZER')) {
            $this->addFlash('error', $translator->trans('flash.error.organizer_no_team'));
            return $this->redirectToRoute('app_error');
        }

        if ($entityManager->getRepository(Team::class)->findOneBy(['name' => $request->request->get('name')])) {
            $this->addFlash('error', $translator->trans('flash.error.team_name_exists'));
            return $this->redirectToRoute('app_team');
        }
        $name = $request->request->get('name');
        $structure = $request->request->get('structure');
        $creator_id = $request->request->get('user_id');
        $creator = $entityManager->getRepository(User::class)->find($creator_id);
        if (empty($name)) {
            $this->addFlash('error', 
            $translator->trans('flash.error.team_name_empty'));
            return $this->redirectToRoute('app_team');
        }
        $team = new Team();
        $team->setName($name)
            ->setStructure($structure)
            ->setCreator($creator);

        $member = new Member();
        $member->setUser($this->getUser())
            ->setTeam($team)
            ->setFname($this->getUser()->getFirstName())
            ->setName($this->getUser()->getName())
            ->setEmail($this->getUser()->getMail());

        $this->getUser()->setMember($member);
        $this->getUser()->getMember()->setTeam($team);
        $team->addMember($member);

        $entityManager->persist($team);
        $entityManager->flush();
        $this->addFlash('success', 
        $translator->trans('createteamsucess'));
        return $this->redirectToRoute('app_team_edit', ['id' => $team->getId()]);
    }

    #[Route('/edit/add_member', name: 'app_team_add_member', methods: ['POST'])]
    public function addMember(EntityManagerInterface $entityManager, Request $request, LoggerInterface $logger, TranslatorInterface $translator): Response
    {
        if ($this->isGranted('ROLE_ORGANIZER')) {
            $teamId = $request->request->get('team_id');
            $team = $entityManager->getRepository(Team::class)->find($teamId);

            if (!$team) {
                throw $this->createNotFoundException($translator->trans('error.team.not_found'));
            }
            $firstMember = $team->getMembers()->first();
            $teamCreator = $firstMember->getUser();
            if (!$teamCreator) {
                throw $this->createNotFoundException($translator->trans('error.team.creator_not_found'));
            }
        } else {
            $user = $this->getUser();
            if (!$user) {
                $logger->alert('User not logged in');
                throw $this->createAccessDeniedException($translator->trans('error.team.must_login'));
            }
            $team = $user->getMember()->getTeam();
            if (!$team) {
                $logger->alert('User has no team');
                throw $this->createAccessDeniedException($translator->trans('error.team.no_team'));
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
            $this->addFlash('success',$addedCount === 1 ? $translator->trans('flash.success.member_added_single'): $translator->trans('flash.success.member_added_multiple', ['%count%' => $addedCount]));
        } else {
            $this->addFlash('info', $translator->trans('flash.info.no_member_added'));
        }

        return $this->redirectToRoute('app_team_edit', [
            'id' => $team->getId()
        ]);
    }

    #[Route('/edit/{id}', name: 'app_team_edit')]
    public function edit(Team $team, EntityManagerInterface $entityManager, Request $request,TranslatorInterface $translator): Response
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

    #[Route('/edit/remove_member/{id}', name: 'app_team_remove_member', methods: ['POST'],)]
    public function removeMember(Member $member, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('You can only remove members from your own team.');
        }

        //if the member is the creator of the team
        if ($member->getTeam()->getCreator() === $member->getUser()) {
            $csrfToken = $this->container->get('security.csrf.token_manager')->getToken('delete' . $member->getTeam()->getId())->getValue();

            $this->addFlash('error', '
<div class="flex flex-col md:flex-row lg:flex-row justify-between items-center gap-4 p-4 sm:p-6">
    <div class="text-center md:text-left lg:text-left text-sm sm:text-base max-w-xl">
        Retirer le créateur de l\'équipe revient à supprimer l\'équipe. Êtes-vous sûr ?
    </div>
    <div class="flex flex-col sm:flex-row md:flex-row lg:flex-row gap-3 sm:gap-4 w-full sm:w-auto">
        <form method="POST" action="' . $this->generateUrl('app_delete_team', ['id' => $member->getTeam()->getId()]) . '" style="display: inline;" class="w-full sm:w-auto">
            <input type="hidden" name="_token" value="' . $csrfToken . '">
            <button 
                type="submit"
                style="background-color: #991b1b; color: white; transition: background-color 0.3s ease;" 
                onmouseover="this.style.backgroundColor=\'#7f1d1d\'" 
                onmouseout="this.style.backgroundColor=\'#991b1b\'" 
                class="w-full sm:w-auto md:w-auto lg:w-auto min-w-[120px] px-4 py-2 rounded-lg text-sm sm:text-base">
                Supprimer l\'équipe
            </button>
        </form>
        <button 
            style="background-color: #10b981; color: white; transition: background-color 0.3s ease;" 
            onmouseover="this.style.backgroundColor=\'#059669\'" 
            onmouseout="this.style.backgroundColor=\'#10b981\'" 
            class="w-full sm:w-auto md:w-auto lg:w-auto min-w-[120px] px-4 py-2 rounded-lg text-sm sm:text-base">
            <a href="' . $this->generateUrl('app_team_edit', ['id' => $member->getTeam()->getId()]) . '">Annuler</a>
        </button>
    </div>
</div>
');

            return $this->redirectToRoute('app_team_edit', ['id' => $member->getTeam()->getId()]);
        }

        $member->getTeam()->removeMember($member);
        $entityManager->remove($member);
        $entityManager->flush();
        $this->addFlash('success', 'Le membre a été retiré avec succès.');
        return $this->redirectToRoute('app_team');
    }

    #[Route('/delete/{id}', name: 'app_delete_team', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete' . $team->getId(), $request->getPayload()->getString('_token'))) {
            $logger->info('Starting team deletion process for team: ' . $team->getName());

            try {
                $versusAsGreen = $entityManager->getRepository(Versus::class)->findBy([
                    'greenTeam' => $team
                ]);
                foreach ($versusAsGreen as $versus) {
                    $versus->setGreenTeam(null);
                    $entityManager->remove($versus);
                }

                $versusAsBlue = $entityManager->getRepository(Versus::class)->findBy([
                    'blueTeam' => $team
                ]);
                foreach ($versusAsBlue as $versus) {
                    $versus->setBlueTeam(null);
                    $entityManager->remove($versus);
                }

                $entityManager->flush();

                foreach ($team->getMembers() as $member) {
                    if ($user = $member->getUser()) {
                        $member->setUser(null);
                        $user->setMember(null);
                        $entityManager->persist($user);
                    }
                    $team->removeMember($member);
                    $entityManager->remove($member);
                }

                foreach ($team->getTeamResults() as $teamResult) {
                    $entityManager->remove($teamResult);
                }

                $entityManager->remove($team);
                $entityManager->flush();

                $logger->info('Team deletion completed successfully');
            } catch (\Exception $e) {
                $logger->error('Error during deletion: ' . $e->getMessage());
                throw $e;
            }
        }

        return $this->redirectToRoute('app_team', [], Response::HTTP_SEE_OTHER);
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
