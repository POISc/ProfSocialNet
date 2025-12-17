<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route('/notifications', name: 'app_notification', methods: ['GET'])]
    public function index(Security $security, NotificationRepository $notificationRepository, UserRepository $userRepository, Request $request, PostRepository $postRepository): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $filter = $request->query->get('filter', 'all');

        switch ($filter) {
            case 'read':
                $notifications = $notificationRepository->findBy([
                    'targetUser' => $user,
                    'isRead' => true,
                ], ['id' => 'DESC']);
                break;

            case 'unread':
                $notifications = $notificationRepository->findBy([
                    'targetUser' => $user,
                    'isRead' => false,
                ], ['id' => 'DESC']);
                break;

            default:
                $notifications = $notificationRepository->findBy([
                    'targetUser' => $user,
                ], ['id' => 'DESC']);
                $filter = 'all';
        }

        $searchTerm = $request->query->get('search', '');
        $foundUsers = [];
        if (!empty($searchTerm)) {
            $foundUsers = $userRepository->serchByNameOrSkills($searchTerm);
        }

        $notificationsData = [];
        foreach ($notifications as $notification) {
            $data = [
                'id' => $notification->getId(),
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

    #[Route('/notification/{id}', name: 'notification_toggle', methods: ['PUT'])]
    public function toggleNotification(Notification $notification, Request $request, Security $security, EntityManagerInterface $em): Response {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('toggle_notification_' . $notification->getId(), $request->request->get('_token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $notification->setIsRead(!$notification->isRead());

        $em->flush();

        return $this->redirectToRoute('app_notification');
    }

    #[Route('/notification/{id}', name: 'notification_delete', methods: ['DELETE'])]
    public function deleteNotification(Notification $notification, Request $request, Security $security): Response {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_notification_' . $notification->getId(), $request->request->get('_token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $em->remove($notification);
        $em->flush();

        return $this->redirectToRoute('app_notification');
    }
}
