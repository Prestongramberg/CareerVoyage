<?php

namespace App\Repository;

use App\Entity\RequestAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RequestAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestAction[]    findAll()
 * @method RequestAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestAction::class);
    }

    // /**
    //  * @return RequestAction[] Returns an array of RequestAction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RequestAction
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
