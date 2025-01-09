<?php

namespace App\Entity;

use App\Repository\FieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FieldRepository::class)]
class Field
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $FLD_NAME = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Championship $CHP_ID = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFLDNAME(): ?string
    {
        return $this->FLD_NAME;
    }

    public function setFLDNAME(string $FLD_NAME): static
    {
        $this->FLD_NAME = $FLD_NAME;

        return $this;
    }

    public function getCHPID(): ?Championship
    {
        return $this->CHP_ID;
    }

    public function setCHPID(?Championship $CHP_ID): static
    {
        $this->CHP_ID = $CHP_ID;

        return $this;
    }
}
