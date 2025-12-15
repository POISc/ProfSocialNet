<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ReactionRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private Security $security;
    private PostRepository $postRepository;
    private ReactionRepository $reactionRepository;

    public function __construct(UserRepository $userRepository, Security $security, PostRepository $postRepository, ReactionRepository $reactionRepository)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->postRepository = $postRepository;
        $this->reactionRepository = $reactionRepository;
    }

    #[Route('/user/{id}', name: 'app_user')]
    public function index(User $user, Request $request, SessionInterface $session): Response
    {
        $searchTerm = $request->query->get('search', '');
        $foundUsers = [];
        if (!empty($searchTerm)) {
            $foundUsers = $this->userRepository->findByNamePartial($searchTerm);
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

        if ($referer) {
            $session->set('return_last_safe_url', $referer);
        }


        return $this->render('user/index.html.twig', [
            'user' => $user,
            'searchTerm' => $searchTerm,
            'foundUsers' => $foundUsers,
            'posts' => $posts,
            'postCounts' => $postCounts,
        ]);
    }

    #[Route('/user/{id}', name: 'user_edit')]
    public function edit(User $user, Request $request): Response
    {
        $searchTerm = $request->query->get('search', '');
        $foundUsers = [];
        if (!empty($searchTerm)) {
            $foundUsers = $this->userRepository->findByNamePartial($searchTerm);
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'searchTerm' => $searchTerm,
            'foundUsers' => $foundUsers,
        ]);
    }
}
