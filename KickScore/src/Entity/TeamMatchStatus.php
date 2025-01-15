<?php

namespace App\Entity;

use App\Repository\TeamMatchStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_TEAM_MATCH_STATUS_TMS')]
#[ORM\Entity(repositoryClass: TeamMatchStatusRepository::class)]
class TeamMatchStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TMS_ID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'teamMatchStatuses')]
    #[ORM\JoinColumn(name: 'MAT_ID', referencedColumnName: 'MAT_ID', nullable: false)]
    private ?Versus $versus = null;

    #[ORM\ManyToOne(inversedBy: 'teamMatchStatuses')]
    #[ORM\JoinColumn(name: 'TEA_ID', referencedColumnName: 'TEA_ID', nullable: false)]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'teamMatchStatuses')]
    #[ORM\JoinColumn(name: 'STS_ID', referencedColumnName: 'STS_ID', nullable: false)]
    private ?Status $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersus(): ?Versus
    {
        return $this->versus;
    }

    public function setVersus(?Versus $versus): static
    {
        $this->versus = $versus;

        return $this;
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

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }
}
