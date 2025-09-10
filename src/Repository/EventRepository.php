<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Event;
use App\Data\SearchDataEvent;
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
    public function findSearchLast(SearchDataEvent $search, QueryBuilder $qb): PaginationInterface
    {
         $query = $qb->getQuery();
         
         return $this->paginator->paginate(
            $query,
            $search->page,
            12
         );
    }

    /**
     * Récupere les evenements en lien avec une recherche
     * @return PaginationInterface
     */
    public function findSearch(SearchDataEvent $search): PaginationInterface
    {
        $query = $this->getSearchQuery($search)->getQuery();
         
        return $this->paginator->paginate(
            $query,
            $search->page,
            9
        );
    }

    
    
    public function getSearchQuery(SearchDataEvent $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.cancelled = :cancelled') // On exclut toutes les courses annulées en filtrant sur le champ "cancelled".
<<<<<<< HEAD
            ->andWhere('e.dateEvent >= :today') // Exclut les courses déjà passées
=======
            ->andWhere('e.dateEvent >= CURRENT_DATE()')
>>>>>>> f0d96636d2ce1c275da83faf73975f2d3d984aff
            ->setParameter('cancelled', false)
            ->setParameter('today', new \DateTime()) // Date actuelle
            ->orderBy('e.dateEvent', 'ASC'); // On trie les résultats par date croissante (les courses les plus proches d'abord)

        if ($search->q) {
            $qb->andWhere('e.name LIKE :q') // On récupère les courses de le nom contiennent 'q' (ce qui est rentré par l'utilisateur)
            ->setParameter('q', '%' . $search->q . '%');
        }

        /**
         * Si la liste des départements n'est pas vide, on extrait les deux premiers caractères du code postal et on les compare à la liste passée dans $search->departements.
         */
        if (!empty($search->departements)) {
            $qb->andWhere('SUBSTRING(e.postalCode, 1, 2) IN (:departements)')
            ->setParameter('departements', $search->departements); 
        }

        /**
         * Si $search->distanceMin est défini et non vide, on ne conserve que les courses dont la distance est supérieure ou égale à cette valeur.
         */
        if ($search->distanceMin != null && $search->distanceMin != "") {
            $qb->andWhere('e.distance >= :distanceMin')
            ->setParameter('distanceMin', $search->distanceMin);
        }

        /**
         * Pareil pour distanceMax, on ne conserve que les courses
         */
        if ($search->distanceMax != null && $search->distanceMax != "") {
            $qb->andWhere('e.distance <= :distanceMax')
            ->setParameter('distanceMax', $search->distanceMax);
        }

        return $qb;
    }


    /**
     * Récupere la distance minimum et maximum des courses
     * @return integer[]
     */
    public function findMinMax(): array
    {
        $result = $this->createQueryBuilder('e')
            ->select('MIN(e.distance) AS min')
            ->addSelect('MAX(e.distance) AS max')
            ->getQuery()
            ->getSingleResult();

        return [(int) $result['min'], (int) $result['max']];
    }
    
    public function getSearchNextEvents (SearchDataEvent $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.organizer', 'u')
            ->addSelect('u')
            ->where('e.dateEvent >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('e.dateEvent', 'ASC');

        return $qb;
    }

    public function getSearchLastEvents (SearchDataEvent $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.organizer', 'u')
            ->addSelect('u')
            ->where('e.dateEvent <= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('e.dateEvent', 'DESC');

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
