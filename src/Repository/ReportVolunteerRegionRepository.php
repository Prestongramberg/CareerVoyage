<?php

namespace App\Repository;

use App\Entity\ReportVolunteerRegion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportVolunteerRegion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportVolunteerRegion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportVolunteerRegion[]    findAll()
 * @method ReportVolunteerRegion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportVolunteerRegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportVolunteerRegion::class);
    }

    // /**
    //  * @return ReportVolunteerRegion[] Returns an array of ReportVolunteerRegion objects
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
    public function findOneBySomeField($value): ?ReportVolunteerRegion
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
