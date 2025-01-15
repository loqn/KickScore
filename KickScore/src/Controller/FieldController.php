<?php

namespace App\Controller;

use App\Entity\Field;
use App\Entity\Championship;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FieldController extends AbstractController
{
    #[Route('/field', name: 'app_field')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get all the championships available
        $championships = $entityManager->getRepository(Championship::class)->findAll();


        $selectedChampionshipId = $request->query->get('select');

        return $this->render(
            'field/index.html.twig',
            [
            'championships' => $championships,
            'select' => $selectedChampionshipId,

            ]
        );
    }

    #[Route('/field/create', name: 'app_field_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException("organizers can't create terrain.");
        }

        //get the championship id from the item select in the form
        $championshipId = $request->request->get('championship');
        $championship = $entityManager->getRepository(Championship::class)->find($championshipId);

        if (!$championship) {
            $this->addFlash('error', 'Erreur : le championnat spécifié est introuvable.');
            return $this->redirectToRoute('app_field');
        }

        $name = $request->request->get('name');
        if (empty($name)) {
            $this->addFlash('error', 'Erreur : le nom du terrain ne peut pas être vide.');
            return $this->redirectToRoute('app_field');
        }

        $field = new Field();
        $field->setName($name);
        $field->setChampionship($championship);
        $entityManager->persist($field);
        $entityManager->flush();

        $this->addFlash('success', 'Le terrain a été créé avec succès.');
        return $this->redirectToRoute('app_field');
    }
}
