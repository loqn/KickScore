<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'T_USER_USR')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['Mail'], message: 'There is already an account with this Mail')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'USR_ID')]
    private ?int $id = null;

    #[ORM\Column(name: 'TEA_ID')]
    private ?int $TeamId = null;

    #[ORM\Column(name: 'USR_FNAME',length: 32, nullable: true)]
    private ?string $FirstName = null;

    #[ORM\Column(name: 'USR_NAME',length: 32, nullable: true)]
    private ?string $Name = null;

    #[ORM\Column(name: 'USR_EMAIL', length: 32)]
    private ?string $Mail = null;

    #[ORM\Column (name: 'USR_ISORG', type: 'boolean')]
    private ?bool $IsOrganizer = null;

    #[ORM\Column(name: 'USR_PASSWORD',length: 32)]
    private ?string $Password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->FirstName;
    }

    public function setFirstName(?string $FirstName): static
    {
        $this->FirstName = $FirstName;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(?string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->Mail;
    }

    public function setMail(string $Mail): static
    {
        $this->Mail = $Mail;

        return $this;
    }

    public function isOrganisator(): ?bool
    {
        return $this->IsOrganizer;
    }

    public function setOrganisator(bool $IsOrganisator): static
    {
        $this->IsOrganizer = $IsOrganisator;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): static
    {
        $this->Password = $Password;

        return $this;
    }

    public function getTeamId(): ?int
    {
        return $this->TeamId;
    }

    public function setTeamId(int $TeamId): static
    {
        $this->TeamId = $TeamId;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->IsOrganizer ? ['ROLE_ORGANIZER'] : ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->Mail;
    }
}
