<?php

namespace Majordome\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Majordome\Entity\Rule;

/**
 * @extends ServiceEntityRepository<Rule>
 *
 * @method Rule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rule[]    findAll()
 * @method Rule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rule::class);
    }

    public function findByName(string $name): ?Rule
    {
        return $this->findOneBy(['name' => $name]);
    }
}
