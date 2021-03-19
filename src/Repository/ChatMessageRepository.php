<?php

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMessage[]    findAll()
 * @method ChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
                ])->orderBy('cm.sentAt', 'DESC')
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

    /**
     * 1. Gets unread message count grouped by sent from user
     * 2. Returns the count within the past hour
     * 3. Returns the count of messages that haven't been marked as read
     * 4. Returns the count of messages that haven't been sent as a notification email yet
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getUnreadMessageCountGroupedBySentFromUser() {
        $query = "select COUNT(cm.id) as unread_messages, sent_from_id as user_sent_from_id, 
                  user_sent_from.first_name as user_sent_from_first_name, user_sent_from.last_name as user_sent_from_last_name,
                  user_sent_from.photo as user_sent_from_photo,
                  user_sent_to.id as user_sent_to_id, user_sent_to.first_name as user_sent_to_first_name, 
                  user_sent_to.email as user_sent_to_email, user_sent_to.last_name as user_sent_to_last_name 
                  from chat_message cm inner join user user_sent_from on cm.sent_from_id = user_sent_from.id 
                  inner join user user_sent_to on cm.sent_to_id = user_sent_to.id
                  WHERE user_sent_to.email is not null and user_sent_to.email != ''
                  /* Get all from within the past hour */
                  and cm.sent_at >= DATE_SUB(NOW(),INTERVAL 1 HOUR)
                  and cm.has_been_read = 0
                  and cm.email_sent = 0
                  group by sent_from_id;";
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

    /**
     * 1. Get total message count for user and additional data
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTotalUnreadMessageCountGroupedBySentToUser() {
        $query = "select COUNT(cm.id) as unread_messages,
                  user_sent_to.id as user_sent_to_id, user_sent_to.first_name as user_sent_to_first_name, 
                  user_sent_to.email as user_sent_to_email, user_sent_to.last_name as user_sent_to_last_name 
                  from chat_message cm inner join user user_sent_to on cm.sent_to_id = user_sent_to.id
                  WHERE user_sent_to.email is not null and user_sent_to.email != ''
                  /* Get all from within the past hour */
                  and cm.sent_at >= DATE_SUB(NOW(),INTERVAL 1 HOUR)
                  and cm.has_been_read = 0
                  and cm.email_sent = 0
                  group by sent_to_id;";
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllUnreadChatMessageIdsInPastHour() {
        $query = "select cm.id from chat_message cm inner join user user_sent_to on cm.sent_to_id = user_sent_to.id
                  WHERE user_sent_to.email is not null and user_sent_to.email != ''
                  /* Get all from within the past hour */
                  and cm.sent_at >= DATE_SUB(NOW(),INTERVAL 1 HOUR)
                  and cm.has_been_read = 0
                  and cm.email_sent = 0";
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }
}
