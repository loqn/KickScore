<?php

namespace App\Repository;

use App\Entity\Championship;
use App\Entity\Versus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Versus>
 */
class VersusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Versus::class);
    }

    public function searchMatches(?string $query, ?int $championshipId): array
    {
        $qb = $this->createQueryBuilder('v')
            ->leftJoin('v.greenTeam', 't1')
            ->leftJoin('v.blueTeam', 't2')
            ->addSelect('t1', 't2');

        if ($championshipId) {
            $qb->andWhere('v.championship = :championship')
                ->setParameter('championship', $championshipId);
        }

        if (!empty($query)) {
            $qb->andWhere('t1.name LIKE :query OR t2.name LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Versus[] Returns an array of Versus objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Versus
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
