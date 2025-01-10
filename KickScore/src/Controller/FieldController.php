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
    public function index(): Response
    {
        return $this->render('field/index.html.twig', [
            'controller_name' => 'FieldController',
        ]);
    }

    #[Route('/field/create', name: 'app_field_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException("organizers can't create teams.");
        }
        //if ($entityManager->getRepository(Field::class)->findOneBy(['FLD_NAME' => $request->request->get('FLD_NAME')])) {
        //    $this->addFlash('error', 'Erreur : un terrain possède déjà ce nom.');
        //    return $this->redirectToRoute('app_field');
        //}
        //get the id of the championship
        $championship = $entityManager->getRepository(Championship::class)->find($request->request->get('id'));
        $name = $request->request->get('name');
        if (empty($name)) {
            $this->addFlash('error', 'Erreur : le nom du terrain ne peut pas être vide.');
            return $this->redirectToRoute('app_field');
        }
        $field = new Field();
        $field->setFLDNAME($name);
        $field->setCHPID($championship);
        $entityManager->persist($team);
        $entityManager->flush();
        $this->addFlash('success', 'Le terrain a été créée avec succès.');
        return $this->redirectToRoute('app_field');
    }
}
