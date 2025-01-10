<?php

namespace App\Repository;

use App\Entity\Championship;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Championship>
 */
class ChampionshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Championship::class);
    }

    public function findTeamsByOrganizer(User $organizer): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('c, t')
            ->join('c.teams', 't')
            ->where('c.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->getQuery()
            ->getResult();

        // Extraction des équipes uniques
        $teams = [];
        foreach ($results as $championship) {
            foreach ($championship->getTeams() as $team) {
                $teams[$team->getId()] = $team;
            }
        }

        return array_values($teams);
    }

    //    /**
    //     * @return Championship[] Returns an array of Championship objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Championship
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
