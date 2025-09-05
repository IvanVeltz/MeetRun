<?php

namespace App\Repository;

use App\Entity\User;
use App\Data\SearchDataRunner;
use Doctrine\ORM\QueryBuilder;
use App\Repository\PostRepository;
use App\Repository\EventRepository;
use App\Repository\TopicRepository;
use App\Repository\FavoriRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\RegistrationEventRepository;
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
    public function __construct(
        ManagerRegistry $registry,
        PaginatorInterface $paginator,
        private TopicRepository $topicRepo,
        private PostRepository $postRepo,
        private EventRepository $eventRepo,
        private FavoriRepository $favoriRepo,
        private RegistrationEventRepository $registrationEventRepo,
    )
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
    public function findSearch(SearchDataRunner $search, User $currentUser): PaginationInterface
    {
         $query = $this->getSearchQuery($search, $currentUser)->getQuery();
         return $this->paginator->paginate(
            $query,
            $search->page,
            12
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
    public function findMinMax(SearchDataRunner $search): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('MIN(DATE_DIFF(CURRENT_DATE(), u.dateOfBirth) / 365) AS min')
            ->addSelect('MAX(DATE_DIFF(CURRENT_DATE(), u.dateOfBirth) / 365) AS max')
            ->getQuery()
            ->getSingleResult();

        return [(int) $result['min'], (int) $result['max']];
    }

    private function getSearchQuery (SearchDataRunner $search, User $currentUser): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
        ->join('u.level', 'l')
        ->addSelect('l')
        ->where('u.deleted = :deleted')
        ->andWhere('u.id != :currentUserId')
        ->setParameter('deleted', false)
        ->setParameter('currentUserId', $currentUser->getId());


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


    public function findNearByUser( float $radius, User $user): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->addSelect('(6371 * acos(cos(radians(:lat)) * cos(radians(u.latitude)) * cos(radians(u.longitude) - radians(:lon)) + sin(radians(:lat)) * sin(radians(u.latitude)))) AS distance')
            ->addSelect('ABS(u.level - :myLevel) AS HIDDEN level_gap')
            ->leftJoin('App\Entity\Follow', 'f', 'WITH', 'f.userSource = :currentUserId AND f.userTarget = u' )
            ->where('u.id != :currentUserId')
            ->andWhere('f.id IS NULL')
            ->having('distance < :radius')
            ->groupBy('u.id')
            ->orderBy('level_gap', 'ASC') 
            ->addOrderBy('distance', 'ASC')
            ->setParameter('lat', $user->getLatitude())
            ->setParameter('lon', $user->getLongitude())
            ->setParameter('myLevel', $user->getLevel())
            ->setParameter('currentUserId', $user->getId())
            ->setParameter('radius', $radius)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $qb;
    }

    public function findLastactionByUser(User $user): array
    {
        $topics = $this->topicRepo->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.isClosed = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        
        $posts = $this->postRepo->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.deleted = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $organizer = $this->eventRepo->createQueryBuilder('e')
            ->where('e.organizer = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $registrations = $this->registrationEventRepo->createQueryBuilder('re')
            ->where('re.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        
        $favoris = $this->favoriRepo->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $actions = array_merge(
            array_map(fn($t) => ['entity' => $t, 'type' => 'topic'], $topics),
            array_map(fn($p) => ['entity' => $p, 'type' => 'post'], $posts),
            array_map(fn($e) => ['entity' => $e, 'type' => 'organizer'], $organizer),
            array_map(fn($re) => ['entity' => $re, 'type' => 'registration'], $registrations),
            array_map(fn($f) => ['entity' => $f, 'type' => 'favori'], $favoris),
        );

        usort($actions, fn($a, $b) => $b['entity']->getCreatedAt() <=> $a['entity']->getCreatedAt());


        return $actions;
    }
}
