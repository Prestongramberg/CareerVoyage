<?php

namespace App\Repository;

use App\Entity\ReportShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportShare[]    findAll()
 * @method ReportShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportShare::class);
    }

    // /**
    //  * @return ReportShare[] Returns an array of ReportShare objects
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
    public function findOneBySomeField($value): ?ReportShare
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
