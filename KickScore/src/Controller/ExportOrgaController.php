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

    #[Route('/download_orga', name: 'download_orga', methods: ['POST'])]
public function downChamp(EntityManagerInterface $entityManager, Request $request, Connection $connection): Response
{
    $selectedChampionshipId = $request->request->get('championship');

    if (!$selectedChampionshipId) {
        throw $this->createNotFoundException('Aucun championnat sélectionné.');
    }

    // Vérifiez si les équipes bleue et verte existent pour les matchs de ce championnat
    $checkTeamsSql = "
        SELECT 
            COUNT(DISTINCT team_blue.TEA_ID) AS blue_team_count,
            COUNT(DISTINCT team_green.TEA_ID) AS green_team_count
        FROM 
            T_MATCH_MAT mat
        LEFT JOIN 
            T_TEAM_TEA team_blue ON mat.TEA_ID = team_blue.TEA_ID
        LEFT JOIN 
            T_TEAM_TEA team_green ON mat.TEA_ID_MAT_TEAMGREEN = team_green.TEA_ID
        WHERE 
            mat.CHP_ID = :id;
    ";

    $teamCounts = $connection->fetchAssociative($checkTeamsSql, [
        'id' => $selectedChampionshipId,
    ]);

    // Initialisez la requête SQL
    $sql = "
        SELECT 
            mat.MAT_ID AS match_id,
            mat.MAT_DATE AS laDate,
            mat.MAT_BLUESCORE AS score_bleu, 
            mat.MAT_GREENSCORE AS score_vert,
            fld.FLD_NAME AS terrain
    ";

    // Ajoutez dynamiquement les parties concernant les équipes si elles existent
    if ($teamCounts['blue_team_count'] > 0) {
        $sql .= ",
            team_blue.TEA_NAME AS equipe_bleue,
            GROUP_CONCAT(mbr_blue.MBR_NAME SEPARATOR ', ') AS nom_equipe_bleue,
            GROUP_CONCAT(mbr_blue.MBR_FNAME SEPARATOR ', ') AS prenom_equipe_bleue,
            GROUP_CONCAT(mbr_blue.MBR_ID SEPARATOR ', ') AS id_equipe_bleue
        ";
    }

    if ($teamCounts['green_team_count'] > 0) {
        $sql .= ",
            team_green.TEA_NAME AS equipe_verte,
            GROUP_CONCAT(mbr_green.MBR_NAME SEPARATOR ', ') AS nom_equipe_verte,
            GROUP_CONCAT(mbr_green.MBR_FNAME SEPARATOR ', ') AS prenom_equipe_verte,
            GROUP_CONCAT(mbr_green.MBR_ID SEPARATOR ', ') AS id_equipe_verte
        ";
    }

    $sql .= "
        FROM 
            T_MATCH_MAT mat
        LEFT JOIN 
            T_TEAM_TEA team_blue ON mat.TEA_ID = team_blue.TEA_ID
        LEFT JOIN 
            T_TEAM_TEA team_green ON mat.TEA_ID_MAT_TEAMGREEN = team_green.TEA_ID
        LEFT JOIN
            T_FIELD_FLD fld ON mat.CHP_ID = fld.CHP_ID
    ";

    if ($teamCounts['blue_team_count'] > 0) {
        $sql .= "
            LEFT JOIN T_MEMBER_MBR mbr_blue ON team_blue.TEA_ID = mbr_blue.TEA_ID
        ";
    }

    if ($teamCounts['green_team_count'] > 0) {
        $sql .= "
            LEFT JOIN T_MEMBER_MBR mbr_green ON team_green.TEA_ID = mbr_green.TEA_ID
        ";
    }

    $sql .= "
        WHERE 
            mat.CHP_ID = :id
        GROUP BY 
            mat.MAT_ID, 
            mat.MAT_DATE, 
            mat.MAT_BLUESCORE, 
            mat.MAT_GREENSCORE
    ";

    if ($teamCounts['blue_team_count'] > 0) {
        $sql .= ", team_blue.TEA_NAME";
    }

    if ($teamCounts['green_team_count'] > 0) {
        $sql .= ", team_green.TEA_NAME";
    }

    $sql .= "
        ORDER BY mat.MAT_DATE;
    ";

    // Exécution de la requête avec les paramètres
    $matches = $connection->fetchAllAssociative($sql, [
        'id' => $selectedChampionshipId
    ]);

    $jsonContent = json_encode($matches, JSON_PRETTY_PRINT);

    // Créer la réponse pour le téléchargement
    $response = new Response($jsonContent);
    $response->headers->set('Content-Type', 'application/json');
    $response->headers->set('Content-Disposition', 'attachment; filename="matches.json"');

    return $response;
}

}