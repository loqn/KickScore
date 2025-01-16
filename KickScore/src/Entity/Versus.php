<?php

namespace App\Entity;

use App\Repository\VersusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TEA_ID_MAT_TEAMBLUE', referencedColumnName: 'TEA_ID')]
    private ?Team $blueTeam = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TEA_ID_MAT_TEAMGREEN', referencedColumnName: 'TEA_ID')]
    private ?Team $greenTeam = null;

    #[ORM\Column(name: 'MAT_GREENSCORE', nullable: true)]
    private ?int $greenScore = null;

    #[ORM\Column(name: 'MAT_BLUESCORE', nullable: true)]
    private ?int $blueScore = null;

    #[ORM\Column(name: 'MAT_DATE', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Championship::class, inversedBy: 'matches')]
    #[ORM\JoinColumn(name: 'CHP_ID', referencedColumnName: 'CHP_ID')]
    private ?Championship $championship = null;

    #[ORM\Column(name:'MAT_COMMENTARY', length: 512, nullable: true)]
    private ?string $commentary = null;

    #[ORM\OneToMany(targetEntity: TeamMatchStatus::class, mappedBy: 'versus')]
    private Collection $teamMatchStatuses;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'STS_ID', referencedColumnName: 'STS_ID', nullable: false)]
    private ?Status $globalStatus = null;

    #[ORM\ManyToOne(targetEntity: Timeslot::class, inversedBy: 'versuses')]
    #[ORM\JoinColumn(name: 'TSL_ID', referencedColumnName: 'TSL_ID', nullable: true)]
    private ?Timeslot $timeslot = null;

    #[ORM\ManyToOne(targetEntity: Field::class, inversedBy: 'versuses')]
    #[ORM\JoinColumn(name: 'FLD_ID', referencedColumnName: 'FLD_ID', nullable: true)]
    private ?Field $field = null;

    public function __construct()
    {
        $this->teamMatchStatuses = new ArrayCollection();
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setBlueTeam(?Team $blueTeam): static
    {
        $this->blueTeam = $blueTeam;

        return $this;
    }

    public function getGreenTeam(): Team
    {
        return $this->greenTeam ?? new Team('Équipe par défaut');
    }
    
    public function getBlueTeam(): Team
    {
        return $this->blueTeam ?? new Team('Équipe par défaut');
    }
    

    public function setGreenTeam(?Team $greenTeam): static
    {
        $this->greenTeam = $greenTeam;

        return $this;
    }

    public function getTeams(): array
    {
        return [$this->blueTeam, $this->greenTeam];
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

    public function getCommentary(): ?string
    {
        return $this->commentary;
    }

    public function setCommentary(?string $commentary): static
    {
        $this->commentary = $commentary;

        return $this;
    }

    /**
     * @return Collection<int, TeamMatchStatus>
     */
    public function getTeamMatchStatuses(): Collection
    {
        return $this->teamMatchStatuses;
    }

    public function addTeamMatchStatus(TeamMatchStatus $teamMatchStatus): static
    {
        if (!$this->teamMatchStatuses->contains($teamMatchStatus)) {
            $this->teamMatchStatuses->add($teamMatchStatus);
            $teamMatchStatus->setVersus($this);
        }

        return $this;
    }

    public function removeTeamMatchStatus(TeamMatchStatus $teamMatchStatus): static
    {
        if ($this->teamMatchStatuses->removeElement($teamMatchStatus)) {
            if ($teamMatchStatus->getVersus() === $this) {
                $teamMatchStatus->setVersus(null);
            }
        }

        return $this;
    }

    public function getGlobalStatus(): ?Status
    {
        return $this->globalStatus;
    }

    public function setGlobalStatus(?Status $globalStatus): static
    {
        $this->globalStatus = $globalStatus;

        return $this;
    }

    public function getTimeslot(): ?Timeslot
    {
        return $this->timeslot;
    }

    public function setTimeslot(?Timeslot $timeslot): static
    {
        $this->timeslot = $timeslot;

        return $this;
    }

    public function getField(): ?Field
    {
        return $this->field;
    }

    public function setField(?Field $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function setStatus(Status $status)
    {
        $this->globalStatus = $status;
    }
}
