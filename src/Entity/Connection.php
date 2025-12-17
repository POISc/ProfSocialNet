<?php

namespace App\Entity;

use App\Enum\ConnectionType;
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

    #[ORM\Column(enumType: ConnectionType::class)]
    private ?ConnectionType $types = null;

    #[ORM\Column]
    private ?int $targetId = null;

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

    public function getTypes(): ?ConnectionType
    {
        return $this->types;
    }

    public function setTypes(ConnectionType $types): static
    {
        $this->types = $types;

        return $this;
    }

    public function getTargetId(): ?int
    {
        return $this->targtargetIdetUser;
    }

    public function setTargetId(?int $targetId): static
    {
        $this->targetId = $targetId;

        return $this;
    }
}
