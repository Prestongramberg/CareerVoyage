<?php

namespace App\Repository;

use App\Entity\SiteAdminRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SiteAdminRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteAdminRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteAdminRequest[]    findAll()
 * @method SiteAdminRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteAdminRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SiteAdminRequest::class);
    }

    // /**
    //  * @return SiteAdminRequest[] Returns an array of SiteAdminRequest objects
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
    public function findOneBySomeField($value): ?SiteAdminRequest
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
