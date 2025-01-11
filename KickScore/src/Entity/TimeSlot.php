<?php

namespace App\Entity;

use App\Repository\TimeSlotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_TIMESLOT_TSL')]
#[ORM\Entity(repositoryClass: TimeSlotRepository::class)]
class TimeSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (name:'TSL_ID')]
    private ?int $id = null;

    #[ORM\Column(name:'TSL_START', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(name:'TSL_END', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    /**
     * @var Collection<int, Versus>
     */
    #[ORM\OneToMany(targetEntity: Versus::class, mappedBy: 'timeslot')]
    private Collection $versuses;

    public function __construct()
    {
        $this->versuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return Collection<int, Versus>
     */
    public function getVersuses(): Collection
    {
        return $this->versuses;
    }

    public function addVersus(Versus $versus): static
    {
        if (!$this->versuses->contains($versus)) {
            $this->versuses->add($versus);
            $versus->setTimeslot($this);
        }

        return $this;
    }

    public function removeVersus(Versus $versus): static
    {
        if ($this->versuses->removeElement($versus)) {
            // set the owning side to null (unless already changed)
            if ($versus->getTimeslot() === $this) {
                $versus->setTimeslot(null);
            }
        }

        return $this;
    }
}
