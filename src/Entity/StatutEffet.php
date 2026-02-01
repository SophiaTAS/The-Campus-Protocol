<?php

namespace App\Entity;

use App\Repository\StatutEffetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatutEffetRepository::class)]
#[ORM\Table(name: 'statut_effet')]
class StatutEffet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private string $nom;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(length: 20)]
    private string $icone;

    #[ORM\Column(type: 'integer')]
    private int $duree;

    #[ORM\Column(name: 'type_effet', length: 50)]
    private string $typeEffet;

    #[ORM\Column(length: 20)]
    private string $cible;

    #[ORM\Column(name: 'cible_stat', length: 20, nullable: true)]
    private ?string $cibleStat = null;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getTypeEffet(): string
    {
        return $this->typeEffet;
    }

    public function setTypeEffet(string $typeEffet): self
    {
        $this->typeEffet = $typeEffet;

        return $this;
    }

    public function getCible(): string
    {
        return $this->cible;
    }

    public function setCible(string $cible): self
    {
        $this->cible = $cible;

        return $this;
    }

    public function getCibleStat(): ?string
    {
        return $this->cibleStat;
    }

    public function setCibleStat(?string $cibleStat): self
    {
        $this->cibleStat = $cibleStat;

        return $this;
    }
}
