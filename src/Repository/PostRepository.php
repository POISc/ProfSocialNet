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

    public function findAllOrderByLikes(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.reactions', 'r', 'WITH', 'r.type = :likeType')
            ->addSelect('COUNT(r.id) AS HIDDEN likesCount')
            ->setParameter('likeType', ReactionType::LIKE)
            ->groupBy('p.id')
            ->orderBy('likesCount', 'DESC')
            ->getQuery()
            ->getResult();
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
