<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    description: 'Utilisateur de l’application',
    type: 'object'
)]
#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[OA\Property(description: 'ID unique de l’utilisateur', type: 'integer', example: 1)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[OA\Property(description: 'Login (email ou identifiant)', type: 'string', example: 'jane.doe')]
    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    private string $login;

    #[OA\Property(description: 'Rôles de l’utilisateur', type: 'array', items: new OA\Items(type: 'string'))]
    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[OA\Property(description: 'Mot de passe hashé', type: 'string', example: '$2y$13$xxx')]
    #[ORM\Column(type: "string")]
    private string $password;

    #[OA\Property(description: 'Statut actif de l’utilisateur', type: 'boolean', example: true)]
    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[OA\Property(description: 'Date de création du compte', type: 'string', format: 'date-time')]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ... Getters and setters (pas besoin de toucher ici)
    // Tu peux garder ceux que tu as déjà.
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);
        return $this;
    }
}
