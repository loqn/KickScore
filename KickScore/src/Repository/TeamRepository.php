<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findAllTeamsByPoints()
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.points', 'DESC')
            ->addOrderBy('t.win', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findTeamsByChampionship(int $championshipId)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.teamResults', 'tr')
            ->where('tr.championship = :championshipId')
            ->setParameter('championshipId', $championshipId)
            ->orderBy('tr.points', 'DESC')
            ->addOrderBy('tr.wins', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findResultsByTeamId(int $teamId)
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'tr', 'c')
            ->leftJoin('t.teamResults', 'tr')
            ->leftJoin('tr.championship', 'c')
            ->where('t.id = :teamId')
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->getResult();
    }
}
