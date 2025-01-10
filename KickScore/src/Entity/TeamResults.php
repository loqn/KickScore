<?php

namespace App\Entity;

use App\Repository\TeamResultsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_TEAMRESULTS_TRS')]
#[ORM\Entity(repositoryClass: TeamResultsRepository::class)]
class TeamResults
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:"TRS_ID")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'teamResults')]
    #[ORM\JoinColumn(name:"TEA_ID", referencedColumnName: "TEA_ID",nullable: false)]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'teamResults')]
    #[ORM\JoinColumn(name:"CHP_ID", referencedColumnName: "CHP_ID", nullable: false)]
    private ?Championship $championship = null;

    #[ORM\Column(name:"TRS_WIN")]
    private ?int $wins = 0;

    #[ORM\Column(name:"TRS_DRAW")]
    private ?int $draws = 0;

    #[ORM\Column(name:"TRS_LOSSES")]
    private ?int $losses = 0;

    #[ORM\Column(name:"TRS_POINTS")]
    private ?int $points = 0;

    #[ORM\Column(name:"TRS_GAMESPLAYED")]
    private ?int $gamesPlayed = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getChampionship(): ?Championship
    {
        return $this->championship;
    }

    public function setChampionship(?Championship $championship): static
    {
        $this->championship = $championship;

        return $this;
    }

    public function getWins(): ?int
    {
        return $this->wins;
    }

    public function setWins(int $wins): static
    {
        $this->wins = $wins;

        return $this;
    }

    public function getDraws(): ?int
    {
        return $this->draws;
    }

    public function setDraws(int $draws): static
    {
        $this->draws = $draws;

        return $this;
    }

    public function getLosses(): ?int
    {
        return $this->losses;
    }

    public function setLosses(int $losses): static
    {
        $this->losses = $losses;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getGamesPlayed(): ?int
    {
        return $this->gamesPlayed;
    }

    public function setGamesPlayed(int $gamesPlayed): static
    {
        $this->gamesPlayed = $gamesPlayed;

        return $this;
    }
}
