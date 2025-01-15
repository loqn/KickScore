<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExportUserController extends AbstractController
{
    #[Route('/export_user', name: 'export_user')]
    public function exportToJson(Connection $connection, EntityManagerInterface $entity): Response
    {
            // Stocking the request
            $team = $this->getUser()->getMember()->getTeam();
        if ($team == null) {
            $msg = "Vous n'avez pas d'équipe.";
            echo '<script type="text/javascript">window.alert("' . $msg . '");</script>';
            return $this->render('root/index.html.twig');
        }
            $name = $this->getUser()->getMember()->getTeam()->getName();
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
                    team_blue.TEA_NAME LIKE :team OR team_green.TEA_NAME LIKE :team
                ORDER BY 
                    mat.MAT_DATE;
            ";
        // Executing the request with parameters
        $data = $connection->fetchAllAssociative($sql, [
            'team' => '%' . $name . '%'
        ]);


        // Encoding data in JSON
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

        // Creating response for download
        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename=\"data.json\"');

        return $response;
    }
}
