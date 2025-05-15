<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class User implements UserInterface
{
   
    private int $id;

    
    private string $githubId;

    private string $accessToken;

    
    private string $username;

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
    return $this->roles ?: ['ROLE_USER'];
}


    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->accessToken;
    }

    public function getUserIdentifier(): string
    {
        return $this->githubId;
    }
}
