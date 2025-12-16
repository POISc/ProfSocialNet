<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NotificationRepository;
use App\Repository\PostRepository;
use App\Entity\Notification;
use Add\Entity\User;
use App\Repository\UserRepository;

final class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notification')]
    public function index(Security $security, NotificationRepository $notificationRepository, UserRepository $userRepository, Request $request, PostRepository $postRepository): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $notifications = $notificationRepository->findBy(
            ['targetUser' => $user],
            ['isRead' => 'DESC']
        );

        $searchTerm = $request->query->get('search', '');
        $foundUsers = [];
        if (!empty($searchTerm)) {
            $foundUsers = $userRepository->findByNamePartial($searchTerm);
        }

        $notificationsData = [];
        foreach ($notifications as $notification) {
            $data = [
                'initiator' => $notification->getInitiator(),
                'eventType' => $notification->getEventType(),
                'isRead' => $notification->isRead(),
                'subject' => null,
            ];

            if ($notification->getSubjectId()) {
                $subject = $postRepository->find($notification->getSubjectId());
                $data['subject'] = $subject;
            }

            $notificationsData[] = $data;
        }

        return $this->render('notification/index.html.twig', [
            'notifications' => $notificationsData,
            'foundUsers' => $foundUsers,
            'searchTerm' => $searchTerm,
        ]);    
    }
}
