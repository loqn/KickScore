<?php

namespace App\Entity;

use App\Repository\FieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_FIELD_FLD')]
#[ORM\Entity(repositoryClass: FieldRepository::class)]
class Field
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'FLD_ID')]
    private ?int $id = null;

    #[ORM\Column(name: 'FLD_NAME', length: 255) ]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Championship::class, inversedBy: 'fields')]
    #[ORM\JoinColumn(name:'CHP_ID', referencedColumnName: 'CHP_ID' ,nullable: false)]
    private ?Championship $championship = null;

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
}
