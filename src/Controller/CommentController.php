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
        $comment->setContent($request->request->get('content'));

        $this->em->persist($comment);
        $this->em->flush();

        $notificationService->notifyUserComment($post, $security->getUser());

        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }
}
