<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Follow;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Follow>
 */
class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    public function areMutuallyFollowing(User $user1, User $user2): bool 
    {
        $qb = $this->createQueryBuilder('f');

        $qb->select('COUNT(f.id)')
            ->where('(f.userSource = :user1 AND f.userTarget = :user2 AND f.followAccepted = true)')
            ->orWhere('(f.userSource = :user2 AND f.userTarget = :user1 AND f.followAccepted = true)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count == 2; // les deux follows doivent être acceptés

    }

    //    /**
    //     * @return Follow[] Returns an array of Follow objects
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

    //    public function findOneBySomeField($value): ?Follow
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
