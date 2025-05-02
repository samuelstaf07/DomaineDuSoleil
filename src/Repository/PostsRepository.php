<?php

namespace App\Repository;

use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Posts>
 */
class PostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Posts::class);
    }

    public function findLatestActivePosts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.is_active = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function findActivePosts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.is_active = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
