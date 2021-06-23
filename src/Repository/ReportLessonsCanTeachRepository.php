<?php

namespace App\Repository;

use App\Entity\ReportLessonsCanTeach;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportLessonsCanTeach|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportLessonsCanTeach|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportLessonsCanTeach[]    findAll()
 * @method ReportLessonsCanTeach[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportLessonsCanTeachRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportLessonsCanTeach::class);
    }

    // /**
    //  * @return ReportLessonsCanTeach[] Returns an array of ReportLessonsCanTeach objects
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
    public function findOneBySomeField($value): ?ReportLessonsCanTeach
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
