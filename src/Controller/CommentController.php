<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\NotificationService;

final class CommentController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/post/{id}/comment-create', name: 'comment_create')]
    public function index(Post $post, Request $request, Security $security, NotificationService $notificationService): Response
    {
        $comment = new Comment();
        $comment->setAuthor($security->getUser());
        $comment->setPost($post);

        $content = trim($request->request->get('content'));
        if ($content === '') {
            throw new BadRequestHttpException('Comment content is required');
        }

        $comment->setContent($content);

        $this->em->persist($comment);
        $this->em->flush();

        $notificationService->notifyUserComment($post, $security->getUser());

        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }

    #[Route('/comment/{id}', name: 'comment_update', methods: ['PUT'])]
    public function updateComment(Comment $comment, Request $request, Security $security): Response {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if ($comment->getAuthor() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'update_comment_' . $comment->getId(),
            $request->request->get('_token')
        )) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $content = trim($request->request->get('content'));
        if ($content === '') {
            throw new BadRequestHttpException('Comment content is required');
        }

        $comment->setContent($content);
        $this->em->flush();

        return $this->redirectToRoute('post_view', [
            'id' => $comment->getPost()->getId(),
        ]);
    }

    #[Route('/comment/{id}/change', name: 'change_comment_page', methods: ['GET'])]
    public function changeComment(Comment $comment, Request $request, Security $security): Response {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if ($comment->getAuthor() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('comment/changeComment.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/comment/{id}', name: 'comment_delete', methods: ['DELETE'])]
    public function deleteComment(Comment $comment, Request $request, Security $security): Response {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if ($comment->getAuthor() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'delete_comment_' . $comment->getId(),
            $request->request->get('_token')
        )) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $postId = $comment->getPost()->getId();

        $this->em->remove($comment);
        $this->em->flush();

        return $this->redirectToRoute('post_view', [
            'id' => $postId,
        ]);
    }
}
