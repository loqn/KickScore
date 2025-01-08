<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Table(name: 'T_TEAM_TEA')]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TEA_ID', type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Championship::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(name: 'CHP_ID', referencedColumnName: 'CHP_ID')]
    private ?Championship $championship = null;

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

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGamePlayed(): ?int
    {
        return $this->gamePlayed;
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
            // set the owning side to null (unless already changed)
            if ($member->getTeam() === $this) {
                $member->setTeam(null);
            }
        }

        return $this;
    }
}
