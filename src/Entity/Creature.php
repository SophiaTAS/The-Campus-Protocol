<?php

namespace App\Entity;

use App\Repository\CreatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreatureRepository::class)]
#[ORM\Table(name: 'creature')]
class Creature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private string $nom;

    #[ORM\Column(name: 'pv_max', type: 'integer')]
    private int $pvMax;

    #[ORM\Column(type: 'integer')]
    private int $attaque;

    #[ORM\Column(name: 'defens', type: 'integer')]
    private int $defens;

    #[ORM\Column(length: 255)]
    private string $image;

    #[ORM\ManyToOne(targetEntity: Type::class, inversedBy: 'creatures')]
    #[ORM\JoinColumn(nullable: false)]
    private Type $type;

    /** @var Collection<int, Attaque> */
    #[ORM\OneToMany(mappedBy: 'creature', targetEntity: Attaque::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $attaques;

    public function __construct()
    {
        $this->attaques = new ArrayCollection();
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

    public function getPvMax(): int
    {
        return $this->pvMax;
    }

    public function setPvMax(int $pvMax): self
    {
        $this->pvMax = $pvMax;

        return $this;
    }

    public function getAttaque(): int
    {
        return $this->attaque;
    }

    public function setAttaque(int $attaque): self
    {
        $this->attaque = $attaque;

        return $this;
    }

    public function getDefens(): int
    {
        return $this->defens;
    }

    public function setDefens(int $defens): self
    {
        $this->defens = $defens;

        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function setType(Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Attaque>
     */
    public function getAttaques(): Collection
    {
        return $this->attaques;
    }

    public function addAttaque(Attaque $attaque): self
    {
        if (!$this->attaques->contains($attaque)) {
            $this->attaques->add($attaque);
            $attaque->setCreature($this);
        }

        return $this;
    }

    public function removeAttaque(Attaque $attaque): self
    {
        $this->attaques->removeElement($attaque);

        return $this;
    }
}
