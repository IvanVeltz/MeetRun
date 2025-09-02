<?php

namespace App\Repository;

use App\Entity\LevelRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LevelRun>
 */
class LevelRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LevelRun::class);
    }

    public function findAllLevels(): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.level')
            ->getQuery()
            ->getResult();
    }

}
