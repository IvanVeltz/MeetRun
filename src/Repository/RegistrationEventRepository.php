<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\RegistrationEvent;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<RegistrationEvent>
 */
class RegistrationEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationEvent::class);
    }

    public function findByUserAndNextEvents(User $user): array
    {
        $now = new \DateTimeImmutable();


        return $this->createQueryBuilder('r')
            ->innerJoin('r.event', 'e')
            ->andWhere('r.user = :user')
            ->andWhere('e.dateEvent > :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndPastEvents(User $user): array
    {
        $now = new \DateTimeImmutable();


        return $this->createQueryBuilder('r')
            ->innerJoin('r.event', 'e')
            ->andWhere('r.user = :user')
            ->andWhere('e.dateEvent < :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function countByEvent(int $id): int
{
    return (int) $this->createQueryBuilder('r')
        ->select('SUM(r.quantity)')
        ->where('r.event = :eventId')
        ->setParameter('eventId', $id)
        ->getQuery()
        ->getSingleScalarResult();
}



    //    /**
    //     * @return RegistrationEvent[] Returns an array of RegistrationEvent objects
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

    //    public function findOneBySomeField($value): ?RegistrationEvent
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
