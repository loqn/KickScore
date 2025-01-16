<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'T_TEAM_TEA')]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TEA_ID', type: "integer")]
    #[Groups(['team:read'])]
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
    #[Groups(['team:read', 'match:read'])]
    private ?string $name = null;

    #[ORM\Column(name: 'TEA_STRUCTURE', length: 32, nullable: true)]
    private ?string $structure = null;

    /**
     * @var Collection<int, Member>
     */
    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'team', cascade: ['persist'])]
    #[Groups(['team:read'])]
    private Collection $members;

    /**
     * @var Collection<int, TeamResults>
     */
    #[ORM\OneToMany(targetEntity: TeamResults::class, mappedBy: 'team')]
    private Collection $teamResults;

    #[ORM\OneToOne(cascade: ['persist'], targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'CREATOR_ID', referencedColumnName: 'USR_ID', nullable: false)]
    private ?User $creator = null;

    /**
     * @var Collection<int, TeamMatchStatus>
     */
    #[ORM\OneToMany(targetEntity: TeamMatchStatus::class, mappedBy: 'team')]
    private Collection $teamMatchStatuses;

    /**
     * @var Collection<int, Tournament>
     */
    #[ORM\ManyToMany(targetEntity: Tournament::class, inversedBy: 'teams')]
    #[ORM\JoinTable(
        name: 'T_TOURNAMENT_TEAM_TRT',
        joinColumns: [
            new ORM\JoinColumn(name: 'TEA_ID', referencedColumnName: 'TEA_ID')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'TRM_ID', referencedColumnName: 'TRM_ID')
        ]
    )]
    private Collection $tournaments;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->championships = new ArrayCollection();
        $this->teamResults = new ArrayCollection();
        $this->teamMatchStatuses = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
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

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): static
    {
        $this->creator = $creator;

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
            $teamMatchStatus->setTeam($this);
        }

        return $this;
    }

    public function removeTeamMatchStatus(TeamMatchStatus $teamMatchStatus): static
    {
        if ($this->teamMatchStatuses->removeElement($teamMatchStatus)) {
            if ($teamMatchStatus->getTeam() === $this) {
                $teamMatchStatus->setTeam(null);
            }
        }

        return $this;
    }

    public function removeAllTeamMatchStatuses(): static
    {
        foreach ($this->teamMatchStatuses as $teamMatchStatus) {
            $this->removeTeamMatchStatus($teamMatchStatus);
        }

        return $this;
    }

    /**
     * @return Collection<int, Tournament>
     */
    public function getTournaments(): Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournament $tournament): static
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments->add($tournament);
            $tournament->addTeam($this);
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament): static
    {
        if ($this->tournaments->removeElement($tournament)) {
            $tournament->removeTeam($this);
        }

        return $this;
    }
}
