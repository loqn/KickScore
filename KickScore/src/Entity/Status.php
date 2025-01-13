<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_STATUS_STS')]
#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (name:'STS_ID')]
    private ?int $id = null;

    #[ORM\Column(name:'STS_NAME', length: 255, nullable: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, TeamMatchStatus>
     */
    #[ORM\OneToMany(targetEntity: TeamMatchStatus::class, mappedBy: 'status')]
    private Collection $teamMatchStatuses;

    public function __construct()
    {
        $this->teamMatchStatuses = new ArrayCollection();
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
            $teamMatchStatus->setStatus($this);
        }

        return $this;
    }

    public function removeTeamMatchStatus(TeamMatchStatus $teamMatchStatus): static
    {
        if ($this->teamMatchStatuses->removeElement($teamMatchStatus)) {
            // set the owning side to null (unless already changed)
            if ($teamMatchStatus->getStatus() === $this) {
                $teamMatchStatus->setStatus(null);
            }
        }

        return $this;
    }
}
