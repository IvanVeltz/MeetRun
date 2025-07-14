<?php

namespace App\Repository;

use App\Entity\User;
use App\Data\SearchData;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, User::class);
        $this->paginator = $paginator;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Récupere les utilsateurs en lien avec une recherche
     * @return PaginationInterface
     */
    public function findSearch(SearchData $search): PaginationInterface
    {
        

         $query = $this->getSearchQuery($search)->getQuery();
         return $this->paginator->paginate(
            $query,
            $search->page,
            10
         );

    }

    public function findDistinctDepartements(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('DISTINCT SUBSTRING(u.postalCode, 1, 2) AS dept')
            ->orderBy('dept', 'ASC');

        $results = $qb->getQuery()->getResult();

        return array_column($results, 'dept'); // extrait les départements en tableau simple
    }

    /**
     * Récupere l'age minimum et maximum des runners
     * @return integer[]
     */
    public function findMinMax(SearchData $search): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('MIN(DATE_DIFF(CURRENT_DATE(), u.dateOfBirth) / 365) AS min')
            ->addSelect('MAX(DATE_DIFF(CURRENT_DATE(), u.dateOfBirth) / 365) AS max')
            ->getQuery()
            ->getSingleResult();

        return [(int) $result['min'], (int) $result['max']];
    }

    private function getSearchQuery (SearchData $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
        ->join('u.level', 'l')
        ->addSelect('l')
        ->where('u.deleted = :deleted')
        ->setParameter('deleted', false);


        if ($search->q) {
            $qb->andWhere('u.firstName LIKE :q OR u.lastName LIKE :q')
            ->setParameter('q', '%' . $search->q . '%');
        }

        if (!empty($search->departements)) {
            $qb->andWhere('SUBSTRING(u.postalCode, 1, 2) IN (:departements)')
            ->setParameter('departements', $search->departements);
        }

        if (!empty($search->levels)) {
            $qb->andWhere('u.level IN (:levels)')
            ->setParameter('levels', $search->levels);
        }

        $today = new \DateTimeImmutable();

        if ($search->ageMin !== null && $search->ageMin !== '') {
            $maxBirthDate = $today->modify('-' . $search->ageMin . ' years');
            $qb->andWhere('u.dateOfBirth <= :maxBirthDate')
            ->setParameter('maxBirthDate', $maxBirthDate);
        }

        if ($search->ageMax !== null && $search->ageMax !== '') {
            $minBirthDate = $today->modify('-' . ($search->ageMax + 1) . ' years')->modify('+1 day');
            $qb->andWhere('u.dateOfBirth >= :minBirthDate')
            ->setParameter('minBirthDate', $minBirthDate);
        }

        if (!empty($search->sexe)) {
            $qb->andWhere('u.sexe IN (:sexe)')
            ->setParameter('sexe', $search->sexe);
        }

        return $qb;
    }

    // public function add(USer $user, bool $flush = false): void
    // {
    //     $this->getEntityManager()->persist($user);

    //     if($flush) {
    //         $this->getEntityManager()->flush();
    //     }
    // }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
