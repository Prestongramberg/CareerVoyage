<?php

namespace App\Repository;

use App\Entity\ReportVolunteerSchool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportVolunteerSchool|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportVolunteerSchool|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportVolunteerSchool[]    findAll()
 * @method ReportVolunteerSchool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportVolunteerSchoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportVolunteerSchool::class);
    }

    // /**
    //  * @return ReportVolunteerSchool[] Returns an array of ReportVolunteerSchool objects
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
    public function findOneBySomeField($value): ?ReportVolunteerSchool
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
