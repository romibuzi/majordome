<?php

namespace Majordome\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Majordome\Entity\Run;
use Majordome\Entity\Violation;

/**
 * @extends ServiceEntityRepository<Violation>
 *
 * @method Violation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Violation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Violation[]    findAll()
 * @method Violation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ViolationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Violation::class);
    }

    /**
     * @param Run $run
     * @return Violation[]
     */
    public function findByRun(Run $run): array
    {
        return $this->findBy(['run' => $run]);
    }
}
