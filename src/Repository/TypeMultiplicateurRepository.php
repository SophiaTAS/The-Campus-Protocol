<?php

namespace App\Repository;

use App\Entity\TypeMultiplicateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeMultiplicateur>
 */
class TypeMultiplicateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeMultiplicateur::class);
    }
}
