<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ChampionshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, ChampionshipRepository $championshipRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'championships' => $championshipRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit-user', $request->request->get('token'))) {
                $this->addFlash('user_error', 'Token de sécurité invalide, veuillez réessayer.');
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            }

            try {
                $user->setFirstName($request->request->get('firstName'))
                    ->setName($request->request->get('name'))
                    ->setMail($request->request->get('email'));

                $password = $request->request->get('password');
                if (!empty($password)) {
                    $password_confirm = $request->request->get('password_confirm');

                    if ($password !== $password_confirm) {
                        $this->addFlash('user_error', 'Les mots de passe ne correspondent pas.');
                        return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
                    }

                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }

                if ($this->isGranted('ROLE_ORGANIZER')) {
                    $user->setIsOrganizer($request->request->has('isOrganizer'));
                }

                $entityManager->flush();

                $this->addFlash('user_success', 'Les modifications ont été enregistrées avec succès.');
                return $this->redirectToRoute('app_user_index');

            } catch (\Exception $e) {
                $this->addFlash('user_error', 'Une erreur est survenue lors de la modification.');
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $logger->info('User deleted: '.$user->getName());
            if ($user->getMember()) {
                $member = $user->getMember();
                $team = $member->getTeam();
                $team->removeMember($member);
                $entityManager->remove($member);
                $user->removeTeam();
            }
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete-team/{id}', name: 'app_delete_team', methods: ['GET'])]
    public function deleteTeam(User $user, EntityManagerInterface $entityManager): Response
    {
        return $this->render('user/delete_team.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/impersonate', name:'app_user_impersonate')]
    public function impersonate(Request $request, User $user): Response
    {
        $targetUrl = $this->generateUrl('app_root');
        return $this->redirect($targetUrl . '?_switch_user=' . $user->getMail());
    }
}
