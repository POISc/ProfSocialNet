<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Connection;
use App\Repository\ConnectionRepository;
use App\Enum\ConnectionType;
use App\Repository\ReactionRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private Security $security;
    private PostRepository $postRepository;
    private ReactionRepository $reactionRepository;
    private ConnectionRepository $connectionRepository;
    

    public function __construct(UserRepository $userRepository, Security $security, PostRepository $postRepository, ReactionRepository $reactionRepository, ConnectionRepository $connectionRepository)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->postRepository = $postRepository;
        $this->reactionRepository = $reactionRepository;
        $this->connectionRepository = $connectionRepository;
    }

    #[Route('/user/{id}', name: 'app_user', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(User $user, Request $request, SessionInterface $session): Response
    {
        $searchTerm = $request->query->get('search', '');
        $foundUsers = [];
        if (!empty($searchTerm)) {
            $foundUsers = $this->userRepository->serchByNameOrSkills($searchTerm);
        }

        $postCounts = [];
        $currentUser = $this->security->getUser();
        $posts = $this->postRepository->getByUser($user);
        foreach ($posts as $post) {
            $postCounts[$post->getId()]['counts'] = $post->getReactionsCount($this->reactionRepository);

            $postCounts[$post->getId()]['currentUserReaction'] = null;

            if($currentUser) {
                $postCounts[$post->getId()]['currentUserReaction'] = $this->reactionRepository->getUserReaction($post, $currentUser)?->getType()?->value;
            }
        }

        $referer = $request->headers->get('referer');
        $session->set('return_last_safe_url', $referer);

        $connection = $this->connectionRepository->findExistingConnection($this->security->getUser(), $user);
        $actionWithConnection = 'Удалить из друзей';
        if($connection) {
            if($connection->getInitiator() === $this->security->getUser()) {
                if($connection->getTypes() === ConnectionType::SUBSCRIBER) {
                    $actionWithConnection = 'Отписаться';
                }
            } else {
                if($connection->getTypes() === ConnectionType::SUBSCRIBER) {
                    $actionWithConnection = 'Добавить в друзья';
                }
            }
        } else {
            $actionWithConnection = 'Подписаться';
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'actionWithConnection' => $actionWithConnection,
            'searchTerm' => $searchTerm,
            'foundUsers' => $foundUsers,
            'posts' => $posts,
            'postCounts' => $postCounts,
        ]);
    }

    #[Route('/user/{id}', name: 'user_edit', methods: ['PUT'])]
    public function edit(User $user, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('edit_user' . $user->getId(), $csrfToken)) {
            throw $this->createAccessDeniedException('Неверный CSRF токен.');
        }

        $fullName = $request->request->get('fullName', $user->getFullName());
        $uuid = $request->request->get('uuid', $user->getUuid());
        $skilsRaw = $request->request->get('skils', '');
        $skils = $skilsRaw !== '' ? array_map('trim', explode(',', $skilsRaw)) : null;
        $description = $request->request->get('description', $user->getDescription());

        $user->setFullName($fullName);
        $user->setUuid($uuid);
        $user->setSkils($skils);
        $user->setDescription($description);

        $em->persist($user);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
