<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findNotification(Notification $notification): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.initiator = :initiator')
            ->andWhere('n.targetUser = :targetUser')
            ->andWhere('n.eventType = :eventType')
            ->andWhere('n.subjectId = :subjectId')
            ->setParameter('initiator', $notification->getInitiator())
            ->setParameter('targetUser', $notification->getTargetUser())
            ->setParameter('eventType', $notification->getEventType())
            ->setParameter('subjectId', $notification->getSubjectId())
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return Notification[] Returns an array of Notification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Notification
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
