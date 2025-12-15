<?php

namespace App\Repository;

use App\Entity\Reaction;
use App\Enum\ReactionType;
use App\Entity\User;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reaction>
 */
class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    public function getUserReaction(Post $post, User $user): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.post = :post')
            ->andWhere('r.initiator = :user')
            ->setParameter('post', $post)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getReactionsCount(Post $post): array
    {
        $qb = $this->createQueryBuilder('r');

        $rows = $qb->select('r.type, COUNT(r.id) AS cnt')
            ->where('r.post = :post')
            ->setParameter('post', $post)
            ->groupBy('r.type')
            ->getQuery()
            ->getResult();

        $result = [
            'likes' => 0,
            'dislikes' => 0,
        ];

        foreach ($rows as $row) {
            if ($row['type'] === ReactionType::LIKE) {
                $result['likes'] = (int) $row['cnt'];
            }

            if ($row['type'] === ReactionType::DISLIKE) {
                $result['dislikes'] = (int) $row['cnt'];
            }
        }

        return $result;
    }

    //    /**
    //     * @return Reaction[] Returns an array of Reaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reaction
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
