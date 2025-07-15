<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Event;
use App\Data\SearchData;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Event::class);
        $this->paginator = $paginator;
    }

    public function findUpcomingEvents(int $limit): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.dateEvent >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('e.dateEvent', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingEventsByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.dateEvent >= :today')
            ->andWhere('e.organizer = :user')
            ->setParameter('today', new \DateTime())
            ->setParameter('user', $user)
            ->orderBy('e.dateEvent', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupere les evenements en lien avec une recherche
     * @return PaginationInterface
     */
    public function findSearch(SearchData $search): PaginationInterface
    {
         $query = $this->getSearchQuery($search)->getQuery();
         return $this->paginator->paginate(
            $query,
            $search->page,
            12
         );
    }

    public function getSearchQuery (SearchData $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.organizer', 'u')
            ->addSelect('u');

        return $qb;
    }


    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
