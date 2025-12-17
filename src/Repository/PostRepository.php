<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\ReactionType;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findAllOrderByRating(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.reactions', 'rl', 'WITH', 'rl.type = :likeType')
            ->setParameter('likeType', ReactionType::LIKE)
            ->leftJoin('p.reactions', 'rd', 'WITH', 'rd.type = :dislikeType')
            ->setParameter('dislikeType', ReactionType::DISLIKE)
            ->leftJoin('p.comments', 'c')
            ->addSelect('COUNT(rl.id) AS HIDDEN likesCount')
            ->addSelect('COUNT(rd.id) AS HIDDEN dislikesCount')
            ->addSelect('COUNT(c.id) AS HIDDEN commentsCount')
            ->addSelect('((COUNT(rl.id) - COUNT(rd.id)) * 10 + COUNT(c.id)) AS HIDDEN rating')
            ->groupBy('p.id')
            ->orderBy('rating', 'DESC');

        return $qb->getQuery()->getResult();
    }


    public function getByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
