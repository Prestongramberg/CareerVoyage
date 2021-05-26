<?php

namespace App\Repository;

use App\Entity\ReportColumn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportColumn|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportColumn|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportColumn[]    findAll()
 * @method ReportColumn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportColumnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportColumn::class);
    }

    // /**
    //  * @return ReportColumn[] Returns an array of ReportColumn objects
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
    public function findOneBySomeField($value): ?ReportColumn
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
