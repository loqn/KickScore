<?php

namespace App\Controller\api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('/search', name: 'api_search_users', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager,LoggerInterface $logger): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        if ($request->query->has('q')) {
            if (strlen($request->query->get('q')) == 0) {
                $users = $entityManager->getRepository(User::class)->findAll();
                return $this->json($users,200, [], ['groups' => 'user:read']);
            }
            $users = $entityManager->getRepository(User::class)->searchUsers($request->query->get('q'));
            $logger->critical('Users found: ' . json_encode($users));
            return $this->json($users, 200, [], ['groups' => 'user:read']);
        }
        return $this->json([], 200);
    }
}