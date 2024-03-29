<?php

namespace App\Repository;

use App\Entity\Chat;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    // /**
    //  * @return Chat[] Returns an array of Chat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Chat
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param User $user
     * @return mixed
     */
    public function findByUser(User $user) {
        return $this->createQueryBuilder('c')
            ->andWhere('c.userOne = :userOne OR c.userTwo = :userTwo')
            ->setParameter('userOne', $user->getId())
            ->setParameter('userTwo', $user->getId())
            ->orderBy('c.updatedAt', 'desc')
            ->getQuery()
            ->getResult();
    }

/*    public function findByUsers($userIds) {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.users','u')
            ->where("u.id IN(:userIds)")
            ->setParameter('userIds', array_values($userIds))
            ->getQuery()
            ->getOneOrNullResult();
    }*/
}
