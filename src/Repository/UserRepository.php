<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use \Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
    * @return User[] Returns an array of User objects
    */
    public function serchByNameOrSkills(string $searchTerm): array
    {
        $em = $this->getEntityManager();

        $searchWords = array_filter(array_map('trim', explode(' ', $searchTerm)));
        $skills = [];

        $sql = "SELECT * FROM user u WHERE 1=1";
        $params = [];

        if (!empty($searchWords)) {
            $nameConditions = [];
            foreach ($searchWords as $i => $word) {
                $nameConditions[] = "u.full_name LIKE :word{$i}";
                $params["word{$i}"] = '%' . $word . '%';
            }
            $sql .= " AND (" . implode(' AND ', $nameConditions) . ")";
        }

        if (!empty($skills)) {
            $skillConditions = [];
            foreach ($skills as $i => $skill) {
                $skillConditions[] = "JSON_CONTAINS(u.skils, :skill{$i})";
                $params["skill{$i}"] = json_encode($skill);
            }
            $sql .= " OR (" . implode(' AND ', $skillConditions) . ")";
        }

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(\App\Entity\User::class, 'u');

        $query = $em->createNativeQuery($sql, $rsm);

        foreach ($params as $key => $val) {
            $query->setParameter($key, $val);
        }

        return $query->getResult();

    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
