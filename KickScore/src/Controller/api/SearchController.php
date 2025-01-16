<?php

namespace App\Controller\api;

use App\Entity\Championship;
use App\Entity\Field;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/')]
class SearchController extends AbstractController
{
    #[Route('/users', name: 'api_search_users', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        if ($request->query->has('q')) {
            if (strlen($request->query->get('q')) == 0) {
                $users = $entityManager->getRepository(User::class)->findAll();
                return $this->json($users, 200, [], ['groups' => 'user:read']);
            }
            $users = $entityManager->getRepository(User::class)->searchUsers($request->query->get('q'));
            return $this->json($users, 200, [], ['groups' => 'user:read']);
        }
        return $this->json([], 200);
    }

    #[Route('/matches', name: 'api_search_match', methods: ['GET'])]
    public function searchMatches(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $query = $request->query->get('q', '');
        $championshipId = $request->query->get('chp');

        $matches = $entityManager->getRepository(Versus::class)->searchMatches($query, $championshipId);

        return $this->json($matches, 200, [], ['groups' => 'match:read']);
    }



    #[Route('/teams', name: 'api_teams_search', methods: ['GET'])]
    public function searchTeams(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');
        if ($request->query->has('q')) {
            if ($request->request->has('chp')) {
                $teams = $entityManager->getRepository(Championship::class)->find($request->query->get('chp'))->getTeams();
                return $this->json($teams, 200, [], ['groups' => 'team:read']);
            }

            $teams = $entityManager->getRepository(Championship::class)->searchTeams($request->query->get('q'));
            return $this->json($teams, 200, [], ['groups' => 'team:read']);
        }
        $teams = $entityManager->getRepository(Team::class)->findAll();
        return $this->json($teams, 200, [], ['groups' => 'team:read']);
    }

    #[Route('/fields', name: 'api_fields_search', methods: ['GET'])]
    public function searchFields(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');
        if ($request->query->has('q')) {
            if ($request->request->has('chp')) {
                $fields = $entityManager->getRepository(Championship::class)->find($request->request->get('chp'))->getFields();
                return $this->json($fields, 200, [], ['groups' => 'field:read']);
            }

            $fields = $entityManager->getRepository(Championship::class)->searchFields($request->query->get('q'));
            return $this->json($fields, 200, [], ['groups' => 'field:read']);
        }
        $fields = $entityManager->getRepository(Field::class)->findAll();
        return $this->json($fields, 200, [], ['groups' => 'field:read']);
    }
}
