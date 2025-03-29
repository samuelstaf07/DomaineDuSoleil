<?php

namespace App\Repository;

use App\Entity\Rentals;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rentals>
 */
class RentalsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rentals::class);
    }

    //    /**
    //     * @return Rentals[] Returns an array of Rentals objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Rentals
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findAllRentalsWithDiscountAndActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.is_active = 1')
            ->andWhere('p.is_on_promotion = 1')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(9)
            ->getQuery()
            ->getResult();
    }

    public function findAllRentalsActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.is_active = 1')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
