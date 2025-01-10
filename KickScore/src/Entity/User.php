<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'T_USER_USR')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['mail'], message: 'There is already an account with this Mail')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'USR_ID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'USR_FNAME',length: 32, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(name: 'USR_NAME',length: 32, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'USR_EMAIL', length: 32)]
    private ?string $mail = null;

    #[ORM\Column (name: 'USR_ISORG', type: 'boolean')]
    private ?bool $isOrganizer = null;

    #[ORM\Column(name: 'USR_PASSWORD',length: 255)]
    private ?string $password = null;

    #[ORM\OneToOne(targetEntity: Member::class, mappedBy: 'user')]
    private ?Member $member = null;

    #[ORM\OneToMany(targetEntity: Championship::class, mappedBy: 'organizer')]
    private Collection $organizedChampionships;

    public function __construct()
    {
        $this->organizedChampionships = new ArrayCollection();
    }

    public function getOrganizedChampionships(): Collection
    {
        return $this->organizedChampionships;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
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

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function isOrganizer(): ?bool
    {
        return $this->isOrganizer;
    }

    public function setIsOrganizer(bool $isOrganizer): static
    {
        $this->isOrganizer = $isOrganizer;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->isOrganizer ? ['ROLE_ORGANIZER'] : ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->mail;
    }

    public function removeMember()
    {
        $this->member = null;
    }
}
