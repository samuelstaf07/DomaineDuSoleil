<?php

namespace App\Repository;

use App\Entity\Bills;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bills>
 */
class BillsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bills::class);
    }

    //    /**
    //     * @return Bills[] Returns an array of Bills objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Bills
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findActiveBillsByUser(\App\Entity\Users $user): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.user = :user')
            ->andWhere('b.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', true)
            ->orderBy('b.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
