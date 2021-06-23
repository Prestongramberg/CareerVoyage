<?php

namespace App\Repository;

use App\Entity\ReportLessonsWantTaught;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportLessonsWantTaught|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportLessonsWantTaught|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportLessonsWantTaught[]    findAll()
 * @method ReportLessonsWantTaught[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportLessonsWantTaughtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportLessonsWantTaught::class);
    }

    // /**
    //  * @return ReportLessonsWantTaught[] Returns an array of ReportLessonsWantTaught objects
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
    public function findOneBySomeField($value): ?ReportLessonsWantTaught
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
