<?php

namespace App\Entity;

use App\Repository\VersusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_MATCH_MAT')]
#[ORM\Entity(repositoryClass: VersusRepository::class)]
class Versus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'MAT_ID')]
    private ?int $id = null;

    #[ORM\Column (name: 'TEA_ID')]
    private ?int $idBlueTeam = null;

    #[ORM\Column (name: 'TEA_ID_MAT_TEAMGREEN')]
    private ?int $idGreenTeam = null;

    #[ORM\Column(name: 'MAT_GREENSCORE', nullable: true)]
    private ?int $greenScore = null;

    #[ORM\Column(name: 'MAT_BLUESCORE' ,nullable: true)]
    private ?int $blueScore = null;

    #[ORM\Column(name: 'MAT_DATE', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdBlueTeam(): ?int
    {
        return $this->idBlueTeam;
    }

    public function setIdBlueTeam(int $idBlueTeam): static
    {
        $this->idBlueTeam = $idBlueTeam;

        return $this;
    }

    public function getIdGreenTeam(): ?int
    {
        return $this->idGreenTeam;
    }

    public function setIdGreenTeam(int $idGreenTeam): static
    {
        $this->idGreenTeam = $idGreenTeam;

        return $this;
    }

    public function getGreenScore(): ?int
    {
        return $this->greenScore;
    }

    public function setGreenScore(?int $greenScore): static
    {
        $this->greenScore = $greenScore;

        return $this;
    }

    public function getBlueScore(): ?int
    {
        return $this->blueScore;
    }

    public function setBlueScore(?int $blueScore): static
    {
        $this->blueScore = $blueScore;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
