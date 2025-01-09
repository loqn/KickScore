<?php

namespace App\Entity;

use App\Repository\TimeSlotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeSlotRepository::class)]
class TimeSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TLS_Start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TSL_END = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTLSStart(): ?\DateTimeInterface
    {
        return $this->TLS_Start;
    }

    public function setTLSStart(\DateTimeInterface $TLS_Start): static
    {
        $this->TLS_Start = $TLS_Start;

        return $this;
    }

    public function getTSLEND(): ?\DateTimeInterface
    {
        return $this->TSL_END;
    }

    public function setTSLEND(\DateTimeInterface $TSL_END): static
    {
        $this->TSL_END = $TSL_END;

        return $this;
    }
}
