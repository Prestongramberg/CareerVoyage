<?php

namespace App\Repository;

use App\Entity\SingleChat;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SingleChat|null find($id, $lockMode = null, $lockVersion = null)
 * @method SingleChat|null findOneBy(array $criteria, array $orderBy = null)
 * @method SingleChat[]    findAll()
 * @method SingleChat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SingleChatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SingleChat::class);
    }

    // /**
    //  * @return SingleChat[] Returns an array of SingleChat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SingleChat
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param User $initiatedBy
     * @param User $user
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByInitiatedByAndUser(User $initiatedBy, User $user)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('(s.initializedBy = :initializedBy AND s.user = :user) OR (s.initializedBy = :user2 AND s.user = :initializedBy2)')
            ->setParameter('initializedBy', $initiatedBy->getId())
            ->setParameter('user', $user->getId())
            ->setParameter('initializedBy2', $user->getId())
            ->setParameter('user2', $initiatedBy->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }





}