<?php

namespace App\Entity;

use App\Repository\PlayoffMatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_PLAYOFF_MATCH_PLA')]
#[ORM\Entity(repositoryClass: PlayoffMatchRepository::class)]
class PlayoffMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'PLA_ID')]
    private ?int $id = null;

    /*
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'nextMatches')]
    #[ORM\JoinTable(name: 'T_PLAYOFF_MATCH_PREVIOUS_PMP',
        joinColumns: [new ORM\JoinColumn(name: 'PMP_SOURCE_ID', referencedColumnName: 'PLA_ID')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'PMP_TARGET_ID', referencedColumnName: 'PLA_ID')]
    )]
    private Collection $previous;
    */
    
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'previous')]
    private Collection $nextMatches;

    #[ORM\OneToOne(mappedBy: 'final', cascade: ['persist', 'remove'])]
    private ?Tournament $tournament = null;

    #[ORM\Column(nullable: true)]
    private ?int $GreenTeamScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $BlueTeamScore = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'pla_status_id', referencedColumnName: 'STS_ID')]
    private ?Status $PLA_Status = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'pla_time_slot_id', referencedColumnName: 'TSL_ID')]
    private ?TimeSlot $PLA_TimeSlot = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'green_team_id', referencedColumnName: 'TEA_ID')]
    private ?Team $GreenTeam = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blue_team_id', referencedColumnName: 'TEA_ID')]
    private ?Team $BlueTeam = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $Date = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $Commentary = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(
        name: 'PLA_PREVIOUS_ONE_ID',
        referencedColumnName: 'PLA_ID',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?self $PreviousMatchOne = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(
        name: 'PLA_PREVIOUS_TWO_ID',
        referencedColumnName: 'PLA_ID',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?self $PreviousMatchTwo = null;
    public function __construct()
    {
        //$this->previous = new ArrayCollection();
        $this->nextMatches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNextMatches(): Collection
    {
        return $this->nextMatches;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        // unset the owning side of the relation if necessary
        if ($tournament === null && $this->tournament !== null) {
            $this->tournament->setFinal(null);
        }

        // set the owning side of the relation if necessary
        if ($tournament !== null && $tournament->getFinal() !== $this) {
            $tournament->setFinal($this);
        }

        $this->tournament = $tournament;

        return $this;
    }

    public function getGreenTeamScore(): ?int
    {
        return $this->GreenTeamScore;
    }

    public function setGreenTeamScore(?int $GreenTeamScore): static
    {
        $this->GreenTeamScore = $GreenTeamScore;

        return $this;
    }

    public function getBlueTeamScore(): ?int
    {
        return $this->BlueTeamScore;
    }

    public function setBlueTeamScore(?int $BlueTeamScore): static
    {
        $this->BlueTeamScore = $BlueTeamScore;

        return $this;
    }

    public function getPLAStatus(): ?Status
    {
        return $this->PLA_Status;
    }

    public function setPLAStatus(?Status $PLA_Status): static
    {
        $this->PLA_Status = $PLA_Status;

        return $this;
    }

    public function getPLATimeSlot(): ?TimeSlot
    {
        return $this->PLA_TimeSlot;
    }

    public function setPLATimeSlot(?TimeSlot $PLA_TimeSlot): static
    {
        $this->PLA_TimeSlot = $PLA_TimeSlot;

        return $this;
    }

    public function getGreenTeam(): ?Team
    {
        return $this->GreenTeam;
    }

    public function setGreenTeam(?Team $GreenTeam): static
    {
        $this->GreenTeam = $GreenTeam;

        return $this;
    }

    public function getBlueTeam(): ?Team
    {
        return $this->BlueTeam;
    }

    public function setBlueTeam(?Team $BlueTeam): static
    {
        $this->BlueTeam = $BlueTeam;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(?\DateTimeInterface $Date): static
    {
        $this->Date = $Date;

        return $this;
    }

    public function getCommentary(): ?string
    {
        return $this->Commentary;
    }

    public function setCommentary(?string $Commentary): static
    {
        $this->Commentary = $Commentary;

        return $this;
    }

    public function getPreviousMatchOne(): ?self
    {
        return $this->PreviousMatchOne;
    }

    public function setPreviousMatchOne(?self $PreviousMatchOne): static
    {
        $this->PreviousMatchOne = $PreviousMatchOne;

        return $this;
    }

    public function getPreviousMatchTwo(): ?self
    {
        return $this->PreviousMatchTwo;
    }

    public function setPreviousMatchTwo(?self $PreviousMatchTwo): static
    {
        $this->PreviousMatchTwo = $PreviousMatchTwo;

        return $this;
    }
}
