<?php

namespace App\Entity;

use App\Repository\StatusRepository;
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
    private ?string $STS_NAME = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSTSNAME(): ?string
    {
        return $this->STS_NAME;
    }

    public function setSTSNAME(?string $STS_NAME): static
    {
        $this->STS_NAME = $STS_NAME;

        return $this;
    }
}
