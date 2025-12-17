<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Connection;
use App\Enum\ConnectionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Connection>
 */
class ConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Connection::class);
    }

    /**
     * @return Connection[] Returns an array of Connection objects
     */
    public function findExistingConnection(User $userA, User $userB): ?Connection
    {
        $subTypes = [
            ConnectionType::SUBSCRIBER,
            ConnectionType::FRIEND
        ];

        return $this->createQueryBuilder('c')
            ->andWhere('(c.userInitiator = :userA AND c.targetId = :userB) OR (c.userInitiator = :userB AND c.targetId = :userA)')
            ->andWhere('c.types IN (:types)')
            ->setParameter('userA', $userA->getId())
            ->setParameter('types', $subTypes)
            ->setParameter('userB', $userB->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingJobRequest(User $initiator, $targetId): ?Connection
    {
        $nonFullTypes = [
            ConnectionType::JOB_REQUEST_USER_TO_COMPANY,
            ConnectionType::JOB_REQUEST_COMPANY_TO_USER
        ];

        return $this->createQueryBuilder('c')
            ->andWhere('c.userInitiator = :initiator')
            ->andWhere('c.targetId = :targetId')
            ->andWhere('c.types IN (:types)')
            ->setParameters([
                'initiator' => $initiator,
                'targetId' => $targetId,
                'types' => $nonFullTypes,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

//    public function findOneBySomeField($value): ?Connection
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
