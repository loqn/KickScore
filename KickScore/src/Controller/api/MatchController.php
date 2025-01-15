<?php

namespace App\Controller\api;

use App\Entity\Versus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/match')]
class MatchController extends AbstractController
{
    #[Route('/search', name: 'api_search_match', methods: ['GET'])]
    public function searchMatches(Request $request, EntityManagerInterface $entityManager,LoggerInterface $logger): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        if ($request->query->has('q')) {
            if (strlen($request->query->get('q')) == 0) {
                $matches = $entityManager->getRepository(Versus::class)->findAll();
                return $this->json($matches,200, [], ['groups' => 'match:read']);
            }
            $matches = $entityManager->getRepository(Versus::class)->searchMatches($request->query->get('q'));
            $logger->critical('Matches found: ' . json_encode($matches));
            return $this->json($matches, 200, [], ['groups' => 'match:read']);
        }
        return $this->json([], 200);
    }
}