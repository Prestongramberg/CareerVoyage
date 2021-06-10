<?php

namespace App\Repository;

use App\Entity\ReportVolunteerRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportVolunteerRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportVolunteerRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportVolunteerRole[]    findAll()
 * @method ReportVolunteerRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportVolunteerRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportVolunteerRole::class);
    }

    // /**
    //  * @return ReportVolunteerRole[] Returns an array of ReportVolunteerRole objects
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
    public function findOneBySomeField($value): ?ReportVolunteerRole
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
