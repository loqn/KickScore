<?php

namespace App\Entity;

use App\Repository\ChampionshipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Table(name: 'T_CHAMPIONSHIP_CHP')]
#[ORM\Entity(repositoryClass: ChampionshipRepository::class)]
class Championship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CHP_ID')]
    private ?int $id = null;

    #[ORM\Column(name: "CHP_NAME", length: 32, nullable: true)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'organizedChampionships')]
    #[ORM\JoinColumn(name: 'USR_ID', referencedColumnName: 'USR_ID')]
    private ?User $organizer = null;

    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'championships')]
    private Collection $teams;

    #[ORM\OneToMany(targetEntity: Versus::class, mappedBy: 'championship')]
    private Collection $matches;

    #[ORM\OneToMany(targetEntity: Field::class, mappedBy: 'championship')]
    private Collection $fields;

    #[ORM\Column(name: 'CHP_DATE_START', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $startDate;

    #[ORM\Column(name: 'CHP_DATE_END', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $endDate;

    /**
     * @var Collection<int, TeamResults>
     */
    #[ORM\OneToMany(targetEntity: TeamResults::class, mappedBy: 'championship')]
    private Collection $teamResults;

    #[ORM\Column(name:'CHP_DESCRIPTION', length: 2048, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(mappedBy: 'championship', cascade: ['persist', 'remove'])]
    private ?Tournament $tournament = null;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->teamResults = new ArrayCollection();
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(Field $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields[] = $field;
            $field->setChampionship($this);
        }
        return $this;
    }

    public function removeField(Field $field): static
    {
        if ($this->fields->removeElement($field)) {
            if ($field->getChampionship() === $this) {
                $field->setChampionship(null);
            }
        }
        return $this;
    }

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->addChampionship($this);
        }
        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            $team->getChampionships()->removeElement($this);
        }
        return $this;
    }

    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(Versus $match): static
    {
        if (!$this->matches->contains($match)) {
            $this->matches[] = $match;
            $match->setChampionship($this);
        }
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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
            $teamResult->setChampionship($this);
        }

        return $this;
    }

    public function removeTeamResult(TeamResults $teamResult): static
    {
        if ($this->teamResults->removeElement($teamResult)) {
            if ($teamResult->getChampionship() === $this) {
                $teamResult->setChampionship(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $date): static
    {
        $this->startDate = $date;
        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $date): static
    {
        $this->endDate = $date;
        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(Tournament $tournament): static
    {
        // set the owning side of the relation if necessary
        if ($tournament->getChampionship() !== $this) {
            $tournament->setChampionship($this);
        }

        $this->tournament = $tournament;

        return $this;
    }
}
