<?php

namespace App\Entity;

use App\Repository\TypeMultiplicateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeMultiplicateurRepository::class)]
#[ORM\Table(name: 'type_multiplicateur')]
class TypeMultiplicateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Type::class)]
    #[ORM\JoinColumn(name: 'type_source_id', referencedColumnName: 'id', nullable: false)]
    private Type $typeSource;

    #[ORM\ManyToOne(targetEntity: Type::class)]
    #[ORM\JoinColumn(name: 'type_cible_id', referencedColumnName: 'id', nullable: false)]
    private Type $typeCible;

    #[ORM\Column(type: 'float')]
    private float $multiplicateur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeSource(): Type
    {
        return $this->typeSource;
    }

    public function setTypeSource(Type $typeSource): self
    {
        $this->typeSource = $typeSource;

        return $this;
    }

    public function getTypeCible(): Type
    {
        return $this->typeCible;
    }

    public function setTypeCible(Type $typeCible): self
    {
        $this->typeCible = $typeCible;

        return $this;
    }

    public function getMultiplicateur(): float
    {
        return $this->multiplicateur;
    }

    public function setMultiplicateur(float $multiplicateur): self
    {
        $this->multiplicateur = $multiplicateur;

        return $this;
    }
}
