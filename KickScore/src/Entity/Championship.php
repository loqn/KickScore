<?php

namespace App\Entity;

use App\Repository\ChampionshipRepository;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'organizedChampionships')]
    #[ORM\JoinColumn(name: 'USR_ID', referencedColumnName: 'USR_ID')]
    private ?User $organizer = null;

    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'championships')]
    private Collection $teams;

    #[ORM\OneToMany(targetEntity: Versus::class, mappedBy: 'championship')]
    private Collection $matches;

    public function __construct()
    {
        $this->teams = new \Doctrine\Common\Collections\ArrayCollection();
        $this->matches = new \Doctrine\Common\Collections\ArrayCollection();
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
