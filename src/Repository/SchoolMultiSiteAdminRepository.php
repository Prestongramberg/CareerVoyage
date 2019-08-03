<?php

namespace App\Repository;

use App\Entity\SchoolMultiSiteAdmin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolMultiSiteAdmin|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolMultiSiteAdmin|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolMultiSiteAdmin[]    findAll()
 * @method SchoolMultiSiteAdmin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolMultiSiteAdminRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolMultiSiteAdmin::class);
    }

    // /**
    //  * @return SchoolMultiSiteAdmin[] Returns an array of SchoolMultiSiteAdmin objects
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
    public function findOneBySomeField($value): ?SchoolMultiSiteAdmin
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
