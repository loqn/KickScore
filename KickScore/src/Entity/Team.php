<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_TEAM_TEA')]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TEA_ID', type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Championship::class, inversedBy: 'teams')]
    #[ORM\JoinTable(
        name: 'T_CHAMPIONSHIP_TEAM_CHT',
        joinColumns: [
            new ORM\JoinColumn(name: 'TEA_ID', referencedColumnName: 'TEA_ID')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'CHP_ID', referencedColumnName: 'CHP_ID')
        ]
    )]
    private Collection $championships;

    #[ORM\Column(name: 'TEA_NAME', length: 32)]
    private ?string $name = null;

    #[ORM\Column(name: 'TEA_STRUCTURE', length: 32, nullable: true)]
    private ?string $structure = null;

    #[ORM\Column(name: 'TEA_GAMEPLAYED', nullable: true)]
    private ?int $gamePlayed = null;

    #[ORM\Column(name: 'TEA_WIN', nullable: true)]
    private ?int $win = null;

    #[ORM\Column(name: 'TEA_DRAW', nullable: true)]
    private ?int $draw = null;

    #[ORM\Column(name: 'TEA_LOSE', nullable: true)]
    private ?int $lose = null;

    #[ORM\Column(name: 'TEA_POINTS', nullable: true)]
    private ?int $points = null;


    /**
     * @var Collection<int, Member>
     */
    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'team', cascade: ['persist'])]
    private Collection $members;

    /**
     * @var Collection<int, TeamResults>
     */
    #[ORM\OneToMany(targetEntity: TeamResults::class, mappedBy: 'team')]
    private Collection $teamResults;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->championships = new ArrayCollection();
        $this->teamResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChampionships(): Collection
    {
        return $this->championships;
    }

    public function addChampionship(Championship $championship): self
    {
        if (!$this->championships->contains($championship)) {
            $this->championships->add($championship);
        }
        return $this;
    }

    public function removeChampionship(Championship $championship): self
    {
        $this->championships->removeElement($championship);
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(?string $structure): static
    {
        $this->structure = $structure;

        return $this;
    }

    public function setGamePlayed(?int $gamePlayed): static
    {
        $this->gamePlayed = $gamePlayed;

        return $this;
    }

    public function getWin(): ?int
    {
        return $this->win;
    }

    public function setWin(?int $win): static
    {
        $this->win = $win;

        return $this;
    }

    public function getDraw(): ?int
    {
        return $this->draw;
    }

    public function setDraw(?int $draw): static
    {
        $this->draw = $draw;

        return $this;
    }

    public function getLose(): ?int
    {
        return $this->lose;
    }

    public function setLose(?int $lose): static
    {
        $this->lose = $lose;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): static
    {
        $this->points = $points;

        return $this;
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setTeam($this);
        }

        return $this;
    }

    public function removeMember(Member $member): static
    {
        if ($this->members->removeElement($member)) {
            if ($member->getTeam() === $this) {
                $member->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TeamResults>
     */
    public function getTeamResults(): Collection
    {
        return $this->teamResults;
    }

    public function addTeamResult(TeamResults $teamResult): static
    {
        if (!$this->teamResults->contains($teamResult)) {
            $this->teamResults->add($teamResult);
            $teamResult->setTeam($this);
        }

        return $this;
    }

    public function removeTeamResult(TeamResults $teamResult): static
    {
        if ($this->teamResults->removeElement($teamResult)) {
            if ($teamResult->getTeam() === $this) {
                $teamResult->setTeam(null);
            }
        }

        return $this;
    }
}
