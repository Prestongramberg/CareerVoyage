<?php

namespace App\Repository;

use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Feedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feedback[]    findAll()
 * @method Feedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    // /**
    //  * @return Feedback[] Returns an array of Feedback objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Feedback
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByEvent($event)
    {
        return $this->createQueryBuilder('f')
                    ->andWhere('f.experience = :event')
                    ->andWhere('f.deleted = :false')
                    ->setParameter('event', $event['id'])
                    ->setParameter('false', false)
                    ->orderBy('f.id', 'ASC')
                    ->getQuery()
                    ->getResult();
    }

    public function getForUser(User $user)
    {
        return $this->createQueryBuilder('f')
                    ->andWhere('f.user = :user')
                    ->andWhere('f.deleted = :deleted')
                    ->setParameter('user', $user)
                    ->setParameter('deleted', false)
                    ->orderBy('f.createdAt', 'DESC')
                    ->getQuery()
                    ->getResult();
    }
}
