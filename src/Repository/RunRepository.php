<?php

namespace Majordome\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Majordome\Entity\Run;

/**
 * @extends ServiceEntityRepository<Run>
 *
 * @method Run|null find($id, $lockMode = null, $lockVersion = null)
 * @method Run|null findOneBy(array $criteria, array $orderBy = null)
 * @method Run[]    findAll()
 * @method Run[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Run::class);
    }

    /**
     * @return Run[] Returns an array of Run objects
     */
    public function findLastRuns(int $limit): array
    {
        return $this->createQueryBuilder('run')
            ->leftJoin('run.violations', 'violations')
            ->addSelect('COUNT(violations.id) AS violationsCount')
            ->groupBy('run.id')
            ->orderBy('run.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
