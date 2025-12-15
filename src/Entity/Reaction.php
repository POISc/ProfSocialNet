<?php

namespace App\Entity;

use App\Enum\ReactionType;
use App\Repository\ReactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReactionRepository::class)]
class Reaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $initiator = null;

    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(enumType: ReactionType::class)]
    private ?ReactionType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInitiator(): ?User
    {
        return $this->initiator;
    }

    public function setInitiator(?User $initiator): static
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getType(): ?ReactionType
    {
        return $this->type;
    }

    public function setType(ReactionType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
