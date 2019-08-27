<?php

namespace App\Repository;

use App\Entity\Chat;
use App\Entity\MessageReadStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MessageReadStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageReadStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageReadStatus[]    findAll()
 * @method MessageReadStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageReadStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MessageReadStatus::class);
    }

    // /**
    //  * @return MessageReadStatus[] Returns an array of MessageReadStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MessageReadStatus
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function getUnreadyMessagesByChatAndUser(Chat $chat, User $user)
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.chatMessage', 'chatMessage')
            ->innerJoin('chatMessage.chat', 'chat')
            ->andWhere('chat.id = :chatId')
            ->andWhere('m.user = :user')
            ->setParameter('chatId', $chat->getId())
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

}
