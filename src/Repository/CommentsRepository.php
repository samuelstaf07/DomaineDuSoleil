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
    public function findCommentsByRentals(Rentals $rental): array
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as commentCount, AVG(c.rating) as averageRating, c')
            ->where('c.rental_id = :rental')
            ->andWhere()
            ->setParameter('rental', $rental)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}
