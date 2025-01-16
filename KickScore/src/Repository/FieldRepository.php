<?php

namespace App\Repository;

use App\Entity\Field;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Field>
 */
class FieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Field::class);
    }

    public function searchFields(string $query, int $championshipId): array
    {
        $qb = $this->createQueryBuilder('f');

        if (!empty($query)) {
            $qb->where('f.name LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($championshipId) {
            $qb->andWhere('f.championship = :championship')
                ->setParameter('championship', $championshipId);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Field[] Returns an array of Field objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Field
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
