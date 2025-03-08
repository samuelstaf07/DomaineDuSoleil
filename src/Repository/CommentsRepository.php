<?php

namespace App\Repository;

use App\Entity\Comments;
use App\Entity\Rentals;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comments>
 */
class CommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comments::class);
    }
    public function findCommentsByRentals(Rentals $rentals): array
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as commentCount, AVG(c.rating) as averageRating, c')
            ->where('c.rentals = :rentals')
            ->andWhere()
            ->setParameter('rentals', $rentals)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}
