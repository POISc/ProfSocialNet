<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Post;
use App\Enum\PostVisibility;
use App\Repository\PostRepository;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Reaction;
use App\Repository\ReactionRepository;
use App\Enum\ReactionType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\NotificationService;

final class PostController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/post/{id}', name: 'post_view', requirements: ['id' => '\d+'])]
    public function index(Post $post, Request $request, PostRepository $postRepository, UserRepository $userRepository, ReactionRepository $reactionRepository, Security $security): Response
    {
        if(\is_null($post)) {
            throw $this->createNotFoundException('Странница не найдена');
        }

        $searchTerm = $request->query->get('search', '');

        $foundUsers = [];
        if (!empty($searchTerm)) {
            $foundUsers = $userRepository->findByNamePartial($searchTerm);
        }

        $postCounts = [$post->getId() => $post->getReactionsCount($reactionRepository)];

        $userReaction = null;
        $user = $security->getUser();
        if ($user) {
            $userReaction = $reactionRepository->getUserReaction($post, $user)?->getType()?->value;
        }


        return $this->render('post/index.html.twig', [
            'post' => $post,
            'foundUsers' => $foundUsers,
            'searchTerm' => $searchTerm,
            'postCounts' => $postCounts,
            'userReaction' => $userReaction,
        ]);
    }

    #[Route('/post/create', name: 'post_create')]
    public function create(Request $request, Security $security): RedirectResponse
    {

        $newPost = new Post();
        $newPost->setContent($request->request->get('content'));
        $newPost->setAuthor($security->getUser());
        $newPost->setVisibility(PostVisibility::PUBLIC);

        $this->em->persist($newPost);
        $this->em->flush();

        return $this->redirect($request->headers->get('referer'));

    }

    #[Route('/post/change/{id}', name: 'post_change', methods: ['PUT'])]
    public function changePost(Post $post, Request $request, Security $security): Response
    {
        if (!$this->isCsrfTokenValid('edit_post' . $post->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        if ($security->getUser() !== $post->getAuthor()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this post');
        }

        $post->setContent($request->request->get('content'));
        $this->em->flush();

        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }

    #[Route('/post/delete/{id}', name: 'post_delete', methods: ['DELETE'])]
    public function deletePost(Post $post, Request $request, Security $security, SessionInterface $session): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token')) && $security->getUser() === $post->getAuthor()) {
            $this->em->remove($post);
            $this->em->flush();
        }

        return $this->redirect($session->get('return_last_safe_url'));
    }

    #[Route('/post/react/{id}', name: 'post_react', methods: ['POST'])]
    public function reaction(Post $post, Request $request, Security $security, ReactionRepository $reactionRepository, NotificationService $notificationService): JsonResponse
    {
        if (!$this->isCsrfTokenValid('react' . $post->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 400);
        }

        $user = $security->getUser();

        $reaction = $reactionRepository->getUserReaction($post, $user);
        $reactionType = ReactionType::from($request->request->get('reactionType'));

        if(is_null($reaction))
        {
            $reaction = new Reaction();
            $reaction->setInitiator($security->getUser());
            $reaction->setPost($post);

            $reaction->setType($reactionType);
            $this->em->persist($reaction);
            $notificationService->notifyUserReaction($reaction);
        }
        else if($reactionType === $reaction->getType()) {
            $this->em->remove($reaction);    
            $reaction = null;
        }
        else {
            $notificationService->notifyUserReaction($reaction);
            $reaction->setType($reactionType);
        }

        $this->em->flush();
        
        $countsReactions = $reactionRepository->getReactionsCount($post);

        return $this->json(['likes' => $countsReactions['likes'],
            'dislikes' => $countsReactions['dislikes'],
            'userReaction' => $reaction?->getType()?->value
        ]);
    }

}
