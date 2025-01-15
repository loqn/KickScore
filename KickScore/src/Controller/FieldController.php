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
use Symfony\Contracts\Translation\TranslatorInterface;

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

        return $this->render(
            'field/index.html.twig',
            [
            'championships' => $championships,
            'select' => $selectedChampionshipId,
            'fields' => $fields
            ]
        );
    }


    #[Route('/field/delete/{id}', name: 'app_delete_field', methods: ['POST'])]
    public function delete(
        Field $field,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ): Response {
        try {
            $championship = $field->getChampionship();
            foreach ($field->getVersuses() as $versus) {
                $this->addFlash('error', $translator->trans('flash.error.cannot_delete_field'));
                return $this->redirectToRoute('app_champ_edit', ['id' => $championship->getId()]);
            }
            $championship->removeField($field);
            $entityManager->remove($field);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('flash.success.field_deleted'));
        } catch (\Exception $e) {
            $logger->error('Error during deletion: ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_champ_edit', ['id' => $championship->getId()]);
    }



    #[Route('/field/create', name: 'app_field_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        if (!$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException($translator->trans('error.field.organizer_only'));
        }
        //cherck if the name of the field is available in the database
        $name = $request->request->get('name');
        $field = $entityManager->getRepository(Field::class)->findOneBy(['name' => $name]);
        //get the championship id from the item select in the form
        $championshipId = $request->request->get('championship');
        $championship = $entityManager->getRepository(Championship::class)->find($championshipId);
        if ($field) {
            $this->addFlash('error', 'Erreur : le terrain spécifié existe déjà.');
            return $this->redirectToRoute('app_champ_edit', ['id' => $championship->getId()]);
        }

        if (!$championship) {
            $this->addFlash('error', $translator->trans('flash.error.championship_not_found'));
            return $this->redirectToRoute('app_champ_edit', ['id' => $championship->getId()]);
        }

        $name = $request->request->get('name');
        if (empty($name)) {
            $this->addFlash('error', $translator->trans('flash.error.field_name_empty'));
            return $this->redirectToRoute('app_champ_edit', ['id' => $championship->getId()]);
        }

        $field = new Field();
        $field->setName($name);
        $field->setChampionship($championship);
        $entityManager->persist($field);
        $entityManager->flush();

        $this->addFlash('success', $translator->trans('flash.success.field_created'));
        return $this->redirectToRoute('app_champ_edit', ['id' => $championship->getId()]);
    }
}
