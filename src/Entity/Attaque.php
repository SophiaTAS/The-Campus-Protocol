<?php

namespace App\Entity;

use App\Repository\AttaqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttaqueRepository::class)]
#[ORM\Table(name: 'attaque')]
class Attaque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private string $nom;

    #[ORM\Column(type: 'integer')]
    private int $degats;

    #[ORM\Column(type: 'float')]
    private float $chance;

    #[ORM\ManyToOne(targetEntity: Creature::class, inversedBy: 'attaques')]
    #[ORM\JoinColumn(nullable: false)]
    private Creature $creature;

    #[ORM\ManyToOne(targetEntity: StatutEffet::class)]
    #[ORM\JoinColumn(name: 'statut_effet_id', referencedColumnName: 'id', nullable: true)]
    private ?StatutEffet $statutEffet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDegats(): int
    {
        return $this->degats;
    }

    public function setDegats(int $degats): self
    {
        $this->degats = $degats;

        return $this;
    }

    public function getChance(): float
    {
        return $this->chance;
    }

    public function setChance(float $chance): self
    {
        $this->chance = $chance;

        return $this;
    }

    public function getCreature(): Creature
    {
        return $this->creature;
    }

    public function setCreature(Creature $creature): self
    {
        $this->creature = $creature;

        return $this;
    }

    public function getStatutEffet(): ?StatutEffet
    {
        return $this->statutEffet;
    }

    public function setStatutEffet(?StatutEffet $statutEffet): self
    {
        $this->statutEffet = $statutEffet;

        return $this;
    }
}
