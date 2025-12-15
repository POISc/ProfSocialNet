<?php

namespace App\Entity;

use App\Enum\PostVisibility;
use App\Repository\PostRepository;
use App\Repository\ReactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?array $attachments = null;

    #[ORM\Column(enumType: PostVisibility::class)]
    private ?PostVisibility $visibility = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, Reaction>
     */
    #[ORM\OneToMany(targetEntity: Reaction::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $reactions;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function setAttachments(?array $attachments): static
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function getVisibility(): ?PostVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(PostVisibility $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reaction>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function getReactionsCount(ReactionRepository $reactionRepository): array
    {
        return $reactionRepository->getReactionsCount($this);
    }


    public function addReaction(Reaction $reaction): static
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions->add($reaction);
            $reaction->setPost($this);
        }

        return $this;
    }

    public function removeReaction(Reaction $reaction): static
    {
        if ($this->reactions->removeElement($reaction)) {
            if ($reaction->getPost() === $this) {
                $reaction->setPost(null);
            }
        }

        return $this;
    }
}
