<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Notification;
use App\Repository\UserRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Reaction;
use App\Enum\ReactionType;
use App\Enum\NotificationType;
use App\Enum\SubjectType;

final class NotificationService
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, NotificationRepository $notificationRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->notificationRepository = $notificationRepository;
    }

    private function addNotification(User $initiator, User $targetUser, NotificationType $eventType, ?int $subjectId): void
    {
        $notification = new Notification();
        $notification->setTargetUser($targetUser);
        $notification->setInitiator($initiator);
        $notification->setEventType($eventType);
        $notification->setSubjectId($subjectId);
        $notification->setIsRead(false);

        if($this->notificationRepository->findNotification($notification)) {
            return;
        }

        $this->em->persist($notification);
        $this->em->flush();
    }

    public function notifyUserReaction(Reaction $reaction): void
    {
        $postOwner = $reaction->getPost()->getAuthor();
        $reactingUser = $reaction->getInitiator();

        if ($postOwner->getId() === $reactingUser->getId()) {
            return;
        }

        $typeNotification = $reaction->getType() === ReactionType::LIKE ? NotificationType::LIKE_TO_POST : NotificationType::DISLIKE_TO_POST;

        $this->addNotification(
            $reactingUser,
            $postOwner,
            $typeNotification,
            $reaction->getPost()->getId()
        );
    }

    public function notifyUserComment(Post $post, User $initiator): void
    {
        $postOwner = $post->getAuthor();

        if ($postOwner->getId() === $initiator->getId()) {
            return;
        }

        $this->addNotification(
            $initiator,
            $postOwner,
            NotificationType::COMMENT_ON_POST,
            $post->getId()
        );
    }

    public function notifyUserSubscribtion(User $target, User $initiator): void
    {
        if ($target->getId() === $initiator->getId()) {
            return;
        }

        $this->addNotification(
            $initiator,
            $target,
            NotificationType::SUBSCRIPTION,
            $initiator->getId()
        );
    }
}
