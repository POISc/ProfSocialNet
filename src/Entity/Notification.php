<?php

namespace App\Entity;

use App\Enum\NotificationType;
use App\Enum\SubjectType;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $targetUser = null;
    
    #[ORM\ManyToOne]
    private ?User $initiator = null;

    #[ORM\Column(enumType: NotificationType::class)]
    private ?NotificationType $eventType = null;
    
    #[ORM\Column(nullable: true)]
    private ?int $subjectId = null;

    #[ORM\Column]
    private ?bool $isRead = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEventType(): ?NotificationType
    {
        return $this->eventType;
    }

    public function setEventType(NotificationType $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getSubjectId(): ?int
    {
        return $this->subjectId;
    }

    public function setSubjectId(int $subjectId): static
    {
        $this->subjectId = $subjectId;

        return $this;
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
}
