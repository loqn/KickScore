<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Match;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class DisplayMeetController extends AbstractController
{
    #[Route('/display/meet', name: 'app_display_meet')]
    public function edit(Championship $championship, EntityManagerInterface $entityManager, Request $request): Response
    {
        //get all the match of the current day 
        $matches = $entityManager->getRepository(Versus::class)->findAll();

        //get the current time 
        $currentTime = new \DateTime('now');

        //make a list of the matches that are currently playing
        $currentMatches = [];
        foreach ($matches as $match) {
            if ($match->getStartTime() <= $currentTime && $match->getEndTime() >= $currentTime) {
                $currentMatches[] = $match;
            }
        }

        //make a list of the matches that are coming up
        $comingMatches = [];
        foreach ($matches as $match) {
            if ($match->getStartTime() > $currentTime) {
                $comingMatches[] = $match;
            }
        }

        return $this->render('display_meet/index.html.twig', [
            'controller_name' => 'DisplayMeetController',
        ]);
    }
}
