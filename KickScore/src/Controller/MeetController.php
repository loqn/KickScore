<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MeetController extends AbstractController
{
    #[Route('/meet', name: 'app_meet')]
    public function index(): Response
    {
        return $this->render('meet/index.html.twig', [
            'controller_name' => 'MeetController',
        ]);
    }

    #[Route('/meet/create', name: 'app_meet_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers can create meets.');
        }
        // Get form data
        $name = $request->request->get('name');
        $isMatch = $request->request->get('isMatch') === 'YES';
        if (empty($name)) {
            $this->addFlash('error', 'Name is required.');
            return $this->redirectToRoute('app_meet');
        }
        // Create championship
        $championship = new Championship();
        $championship->setName($name);
        $championship->setIsMatch($isMatch);
        // Handle match-specific data if it's a match
        if ($isMatch) {
            $matchType = $request->request->get('type');
            $greenScore = $request->request->get('greenscore');
            $blueScore = $request->request->get('bluescore');
            if (!$matchType || !$greenScore || !$blueScore) {
                $this->addFlash('error', 'All match fields are required when creating a match.');
                return $this->redirectToRoute('app_meet');
            }
            $championship->setMatchType($matchType);
            $championship->setGreenScore((int) $greenScore);
            $championship->setBlueScore((int) $blueScore);
        }
        $entityManager->persist($championship);
        $entityManager->flush();
        $this->addFlash('success', 'Championship created successfully!');
        return $this->redirectToRoute('app_meet');
    }
}