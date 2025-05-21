<?php

namespace App\Repository;

use App\Entity\Comments;
use App\Entity\Rentals;
use App\Entity\ReservationsRentals;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationsRentals>
 */
class ReservationsRentalsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationsRentals::class);
    }

    //    /**
    //     * @return ReservationsRentals[] Returns an array of ReservationsRentals objects
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

    //    public function findOneBySomeField($value): ?ReservationsRentals
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findCurrentAndUpcomingByUser(Users $user): array
    {
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('r')
            ->join('r.bill', 'b')
            ->andWhere('r.date_end >= :today')
            ->andWhere('r.status_reservation = true')
            ->andWhere('r.user = :user')
            ->andWhere('b.status = :active')
            ->setParameter('today', $today)
            ->setParameter('user', $user)
            ->setParameter('active', true)
            ->orderBy('r.date_start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFinishedReservations(Users $user): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.rentals', 'rental')
            ->where('r.user = :user')
            ->andWhere('r.date_end < :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable());

        return $qb->getQuery()->getResult();
    }

    public function findByUserAndRental(Users $user, Rentals $rental): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.rentals = :rental')
            ->setParameter('user', $user)
            ->setParameter('rental', $rental)
            ->orderBy('r.date_reservation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
