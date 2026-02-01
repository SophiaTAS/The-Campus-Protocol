<?php

namespace App\Repository;

use App\Entity\Creature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Creature>
 */
class CreatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creature::class);
    }

    /**
     * @return array<int, Creature>
     */
    public function findAllWithType(): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('t')
            ->leftJoin('c.type', 't')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneWithAttaquesAndType(int $id): ?Creature
    {
        return $this->createQueryBuilder('c')
            ->addSelect('t', 'a')
            ->leftJoin('c.type', 't')
            ->leftJoin('c.attaques', 'a')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, Creature>
     */
    public function findManyWithAttaquesAndType(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $results = $this->createQueryBuilder('c')
            ->addSelect('t', 'a')
            ->leftJoin('c.type', 't')
            ->leftJoin('c.attaques', 'a')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', array_values($ids))
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($results as $creature) {
            $map[$creature->getId()] = $creature;
        }

        return $map;
    }
}
