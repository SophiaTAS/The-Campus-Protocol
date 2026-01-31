<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
#[ORM\Table(name: 'type')]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $nom;

    #[ORM\Column(length: 20)]
    private string $icone;

    #[ORM\Column(length: 20)]
    private string $couleur;

    /** @var Collection<int, Creature> */
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Creature::class, orphanRemoval: true)]
    private Collection $creatures;

    public function __construct()
    {
        $this->creatures = new ArrayCollection();
    }

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

    public function getIcone(): string
    {
        return $this->icone;
    }

    public function setIcone(string $icone): self
    {
        $this->icone = $icone;

        return $this;
    }

    public function getCouleur(): string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * @return Collection<int, Creature>
     */
    public function getCreatures(): Collection
    {
        return $this->creatures;
    }

    public function addCreature(Creature $creature): self
    {
        if (!$this->creatures->contains($creature)) {
            $this->creatures->add($creature);
            $creature->setType($this);
        }

        return $this;
    }

    public function removeCreature(Creature $creature): self
    {
        $this->creatures->removeElement($creature);

        return $this;
    }
}
