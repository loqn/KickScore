<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'T_MEMBER_MBR')]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'MBR_ID')]
    private ?int $id = null;

    #[ORM\Column(name: 'MBR_FNAME', length: 32)]
    private ?string $fname = null;

    #[ORM\Column(name: 'MBR_NAME', length: 32)]
    private ?string $name = null;

    #[ORM\Column(name: 'MBR_EMAIL', length: 255)]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(name: 'TEA_ID', referencedColumnName: 'TEA_ID', nullable: false)]
    private ?Team $team = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'member')]
    #[ORM\JoinColumn(name: 'USR_ID', referencedColumnName: 'USR_ID', nullable: true)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFname(): ?string
    {
        return $this->fname;
    }

    public function setFname(string $fname): static
    {
        $this->fname = $fname;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
