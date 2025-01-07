<?php

namespace App\Entity;

use App\Repository\ChampionshipRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_CHAMPIONSHIP_CHP')]
#[ORM\Entity(repositoryClass: ChampionshipRepository::class)]
class Championship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (name: 'CHP_ID')]
    private ?int $id = null;

    #[ORM\Column(name: "CHP_NAME", length: 32, nullable: true)]
    private ?string $Name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'USR_ID', referencedColumnName: 'USR_ID')]
    private ?User $organizer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(?string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }
}
