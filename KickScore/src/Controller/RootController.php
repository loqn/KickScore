<?php

namespace App\Controller;

use App\Entity\Championship;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RootController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function index(): Response
    {
        return $this->render('root/index.html.twig', [
            'controller_name' => 'RootController',
        ]);
    }

    #[Route('/match', name: 'app_match_list')]
    public function matchList(EntityManagerInterface $entityManager): Response
    {
        //get all championships by ordering them by date
        $championships = $entityManager->getRepository(Championship::class)->findBy([], ['date' => 'DESC']);

        return $this->render('match/match.html.twig', [
            'controller_name' => 'RootController',
            'championships' => $championships
        ]);
    }
}
