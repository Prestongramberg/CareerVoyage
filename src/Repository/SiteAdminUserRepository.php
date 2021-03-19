<?php

namespace App\Repository;

use App\Entity\SiteAdminUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SiteAdminUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteAdminUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteAdminUser[]    findAll()
 * @method SiteAdminUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteAdminUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteAdminUser::class);
    }

    // /**
    //  * @return SiteAdminUser[] Returns an array of SiteAdminUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SiteAdminUser
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
