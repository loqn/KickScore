<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_TOURNAMENT_TRM')]
#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TRM_ID')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $TRM_NAME = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TRM_START = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TRM_END = null;

    #[ORM\OneToOne(targetEntity: Championship::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'CHP_ID', referencedColumnName: 'CHP_ID', nullable: false)]
    private ?Championship $championship = null;

    #[ORM\OneToOne(targetEntity: PlayoffMatch::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'PLA_ID', referencedColumnName: 'PLA_ID', nullable: true)]
    private ?PlayoffMatch $final = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTRMNAME(): ?string
    {
        return $this->TRM_NAME;
    }

    public function setTRMNAME(string $TRM_NAME): static
    {
        $this->TRM_NAME = $TRM_NAME;

        return $this;
    }

    public function getTRMSTART(): ?\DateTimeInterface
    {
        return $this->TRM_START;
    }

    public function setTRMSTART(\DateTimeInterface $TRM_START): static
    {
        $this->TRM_START = $TRM_START;

        return $this;
    }

    public function getTRMEND(): ?\DateTimeInterface
    {
        return $this->TRM_END;
    }

    public function setTRMEND(\DateTimeInterface $TRM_END): static
    {
        $this->TRM_END = $TRM_END;

        return $this;
    }

    public function getChampionship(): ?Championship
    {
        return $this->championship;
    }

    public function setChampionship(Championship $championship): static
    {
        $this->championship = $championship;

        return $this;
    }

    public function getFinal(): ?PlayoffMatch
    {
        return $this->final;
    }

    public function setFinal(?PlayoffMatch $final): static
    {
        $this->final = $final;

        return $this;
    }
}
