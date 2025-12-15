<?php

namespace App\Entity;

use App\Repository\ConnectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConnectionRepository::class)]
class Connection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'connections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userInitiator = null;

    #[ORM\Column(length: 50)]
    private ?string $types = null;

    #[ORM\ManyToOne(inversedBy: 'incomingConnection')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $targetUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInitiator(): ?User
    {
        return $this->userInitiator;
    }

    public function setUserInitiator(?User $userInitiator): static
    {
        $this->userInitiator = $userInitiator;

        return $this;
    }

    public function getTypes(): ?string
    {
        return $this->types;
    }

    public function setTypes(string $types): static
    {
        $this->types = $types;

        return $this;
    }

    public function getTargetUser(): ?User
    {
        return $this->targetUser;
    }

    public function setTargetUser(?User $targetUser): static
    {
        $this->targetUser = $targetUser;

        return $this;
    }
}
