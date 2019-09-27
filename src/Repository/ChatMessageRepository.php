<?php

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMessage[]    findAll()
 * @method ChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    // /**
    //  * @return ChatMessage[] Returns an array of ChatMessage objects
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
    public function findOneBySomeField($value): ?ChatMessage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */



    public function findUnreadMessagesByUser(User $user)
    {
        $qb = $this->createQueryBuilder('cm')
            ->select('DISTINCT sentFrom.id as sent_from, sentFrom.firstName as first_name, sentFrom.lastName as last_name, COUNT(cm.id) as unread_messages' )
            ->join('cm.sentTo', 'sentTo')
            ->join('cm.sentFrom', 'sentFrom')
            ->andWhere('cm.sentTo = :sentTo')
            ->groupBy('cm.sentFrom')
            ->setParameters(['sentTo' => $user]);

        $query = $qb->getQuery();
        $results = $query->getArrayResult();

        foreach($results as &$result) {

            $result['chat_messages'] = [];

            $chatMessages = $this->createQueryBuilder('cm')
                ->select('cm.sentAt, cm.body')
                ->andWhere('cm.sentFrom = :sentFrom')
                ->andWhere('cm.sentTo = :sentTo')
                ->setParameters([
                    'sentTo' => $user,
                    'sentFrom' => $result['sent_from']
                ])
                ->getQuery()
                ->getArrayResult();

            foreach($chatMessages as $chatMessage) {
                $result['chat_messages'][] = [
                  'body' => $chatMessage['body'],
                  'sent_at' => $chatMessage['sentAt']->format('Y-m-d g:i A')
                ];
            }
        }

        return $results;
    }
}
