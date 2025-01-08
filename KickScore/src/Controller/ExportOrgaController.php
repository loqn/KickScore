<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Championship;
use App\Entity\Versus;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;


class ExportOrgaController extends AbstractController
{
    #[Route('/export_orga', name: 'export_orga')]
    public function selectChampionship(EntityManagerInterface $entityManager, Request $request,Connection $connection): Response
    {
        // Récupérer tous les championnats
        $championships = $entityManager->getRepository(Championship::class)->findAll();

        // Vérifier si un championnat a été sélectionné
        $selectedChampionshipId = $request->request->get('championship');
        

        return $this->render('org/select_champ.html.twig', [
            'championships' => $championships,
            'select' => $selectedChampionshipId,
        ]);
    }

    #[Route('/download_orga', name: 'download_orga', methods:'post')]
    public function downChamp(EntityManagerInterface $entityManager, Request $request, Connection $connection): Response
    {
        $selectedChampionshipId = $request->request->get('championship');

        if (!$selectedChampionshipId) {
            throw $this->createNotFoundException('Aucun championnat sélectionné.');
        }
        $sql = "
                SELECT 
                    mat.MAT_ID AS match_id,
                    mat.MAT_DATE AS laDate,
                    team_blue.TEA_NAME AS equipe_bleue,
                    team_green.TEA_NAME AS equipe_verte,
                    mat.MAT_BLUESCORE AS score_bleu, 
                    mat.MAT_GREENSCORE AS score_vert
                FROM 
                    T_MATCH_MAT mat
                JOIN 
                    T_TEAM_TEA team_blue ON mat.TEA_ID = team_blue.TEA_ID
                JOIN 
                    T_TEAM_TEA team_green ON mat.TEA_ID_MAT_TEAMGREEN = team_green.TEA_ID
                WHERE 
                    mat.CHP_ID = :id
                ORDER BY 
                    mat.MAT_DATE;
            ";
        // Executing the request with parameters
        $matches = $connection->fetchAllAssociative($sql, [
            'id' => $selectedChampionshipId
        ]);

        $jsonContent = json_encode($matches, JSON_PRETTY_PRINT);

        // Creating response for download
        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="matches.json"');

        return $response;
    }
}