<?php

namespace App\Repository;

use App\Entity\StatutEffet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatutEffet>
 */
class StatutEffetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatutEffet::class);
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, StatutEffet>
     */
    public function findByIds(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $results = $this->createQueryBuilder('s')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', array_values($ids))
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($results as $statut) {
            $map[$statut->getId()] = $statut;
        }

        return $map;
    }
}
