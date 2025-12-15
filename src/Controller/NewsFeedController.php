<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ReactionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Enum\PostVisibility;

final class NewsFeedController extends AbstractController
{
    

    #[Route('/feed', name: 'app_news_feed')]
    public function index(PostRepository $postRepository, UserRepository $userRepository, Request $request, ReactionRepository $reactionRepository, Security $security, SessionInterface $session): Response
    {
        $posts = $postRepository->findAllOrderByLikes();

        $postCounts = [];
        $user = $security->getUser();
        foreach ($posts as $post) {
            $postCounts[$post->getId()]['counts'] = $post->getReactionsCount($reactionRepository);

            $postCounts[$post->getId()]['currentUserReaction'] = null;

            if($user) {
                $postCounts[$post->getId()]['currentUserReaction'] = $reactionRepository->getUserReaction($post, $user)?->getType()?->value;
            }
        }
        
        $searchTerm = $request->query->get('search', '');
        $foundUsers = [];
        if (!empty($searchTerm)) {
            $foundUsers = $userRepository->findByNamePartial($searchTerm);
        }

        $referer = $request->headers->get('referer');
        $session->set('return_last_safe_url', $referer);

        return $this->render('news_feed/index.html.twig', [
            'posts' => $posts,
            'foundUsers' => $foundUsers,
            'searchTerm' => $searchTerm,
            'postCounts' => $postCounts,
        ]);
    }
}
