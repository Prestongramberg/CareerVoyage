<?php

namespace App\Repository;

use App\Entity\RequestPossibleApprovers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RequestPossibleApprovers|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestPossibleApprovers|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestPossibleApprovers[]    findAll()
 * @method RequestPossibleApprovers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestPossibleApproversRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestPossibleApprovers::class);
    }

    // /**
    //  * @return RequestPossibleApprovers[] Returns an array of RequestPossibleApprovers objects
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
    public function findOneBySomeField($value): ?RequestPossibleApprovers
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
