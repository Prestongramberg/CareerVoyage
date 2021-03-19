<?php

namespace App\Repository;

use App\Entity\CompanyResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyResource[]    findAll()
 * @method CompanyResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyResource::class);
    }

    // /**
    //  * @return CompanyResource[] Returns an array of CompanyResource objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompanyResource
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
