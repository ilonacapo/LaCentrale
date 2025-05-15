<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", unique: true)]
    #[Assert\NotBlank]
    private ?string $githubId = null;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank]
    private ?string $accessToken = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGithubId(): ?string
    {
        return $this->githubId;
    }

    public function setGithubId(string $githubId): self
    {
        $this->githubId = $githubId;
        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return null; // Pas de mot de passe pour OAuth2
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->accessToken = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->githubId;
    }
}
