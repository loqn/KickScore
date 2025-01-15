<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Field;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class FieldController extends AbstractController
{
    #[Route('/field', name: 'app_field')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get all the championships available
        $championships = $entityManager->getRepository(Championship::class)->findAll();

        //get all the fields
        $fields = $entityManager->getRepository(Field::class)->findAll();

        //get the selected championship id
        $selectedChampionshipId = $request->query->get('select');

        return $this->render('field/index.html.twig', [
            'championships' => $championships,
            'select' => $selectedChampionshipId,
            'fields' => $fields
        ]);
    }


    #[Route('/field/delete/{id}', name: 'app_delete_field', methods: ['POST'])]
    public function delete(Request $request, Field $field, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        try {
            $championship = $field->getChampionship();
            $championship->removeField($field);
            $entityManager->remove($field);
            $entityManager->flush();
            $this->addFlash('success', 'Le terrain a été supprimé avec succès');
        } catch (\Exception $e) {
            $logger->error('Error during deletion: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_champ_edit', ['id' => $championship->getId()]);
    }



    #[Route('/field/create', name: 'app_field_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException("organizers can't create terrain.");
        }
        //cherck if the name of the field is available in the database 
        $name = $request->request->get('name');
        $field = $entityManager->getRepository(Field::class)->findOneBy(['name' => $name]);
        if ($field) {
            $this->addFlash('error', 'Erreur : le terrain spécifié existe déjà.');
            return $this->redirectToRoute('app_champ_edit',['id' => $championship->getId()]);
        }
        //get the championship id from the item select in the form
        $championshipId = $request->request->get('championship');
        $championship = $entityManager->getRepository(Championship::class)->find($championshipId);

        if (!$championship) {
            $this->addFlash('error', 'Erreur : le championnat spécifié est introuvable.');
            return $this->redirectToRoute('app_champ_edit',['id' => $championship->getId()]);
        }
        $name = $request->request->get('name');
        if (empty($name)) {
            $this->addFlash('error', 'Erreur : le nom du terrain ne peut pas être vide.');
            return $this->redirectToRoute('app_champ_edit',['id' => $championship->getId()]);
        }

        $field = new Field();
        $field->setName($name);
        $field->setChampionship($championship);
        $entityManager->persist($field);
        $entityManager->flush();

        $this->addFlash('success', 'Le terrain a été créé avec succès.');
        return $this->redirectToRoute('app_champ_edit',['id' => $championship->getId()]);
    }
}
