<?php

namespace App\Entity;

use App\Repository\FieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'T_FIELD_FLD')]
#[ORM\Entity(repositoryClass: FieldRepository::class)]
class Field
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'FLD_ID')]
    #[Groups(['match:read'])]
    private ?int $id = null;

    #[ORM\Column(name: 'FLD_NAME', length: 255) ]
    #[Groups(['field:read', 'match:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Championship::class, inversedBy: 'fields')]
    #[ORM\JoinColumn(name:'CHP_ID', referencedColumnName: 'CHP_ID', nullable: false)]
    private ?Championship $championship = null;

    /**
     * @var Collection<int, Versus>
     */
    #[ORM\OneToMany(targetEntity: Versus::class, mappedBy: 'field')]
    private Collection $versuses;

    public function __construct()
    {
        $this->versuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getChampionship(): ?Championship
    {
        return $this->championship;
    }

    public function setChampionship(?Championship $championship): static
    {
        $this->championship = $championship;

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
            $versus->setField($this);
        }

        return $this;
    }

    public function removeVersus(Versus $versus): static
    {
        if ($this->versuses->removeElement($versus)) {
            // set the owning side to null (unless already changed)
            if ($versus->getField() === $this) {
                $versus->setField(null);
            }
        }

        return $this;
    }
}
