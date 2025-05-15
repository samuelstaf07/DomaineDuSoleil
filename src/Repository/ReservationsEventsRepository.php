<?php

namespace App\Repository;

use App\Entity\Events;
use App\Entity\ReservationsEvents;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationsEvents>
 */
class ReservationsEventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationsEvents::class);
    }

    //    /**
    //     * @return ReservationsEvents[] Returns an array of ReservationsEvents objects
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

    //    public function findOneBySomeField($value): ?ReservationsEvents
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getTotalPlacesByUserAndEvent(int $userId, int $eventId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('SUM(r.nb_places)')
            ->where('r.user = :userId')
            ->andWhere('r.event = :eventId')
            ->andWhere('r.is_active = true')
            ->setParameter('userId', $userId)
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findUpcomingReservationsByUser(Users $user): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.event', 'e')
            ->join('r.bill', 'b')
            ->where('r.user = :user')
            ->andWhere('e.date >= :today')
            ->andWhere('b.status = :active')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->setParameter('active', true);

        return $qb->getQuery()->getResult();
    }

}
