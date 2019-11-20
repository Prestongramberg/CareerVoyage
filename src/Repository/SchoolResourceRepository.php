<?php

namespace App\Repository;

use App\Entity\SchoolResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolResource[]    findAll()
 * @method SchoolResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolResourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolResource::class);
    }

    // /**
    //  * @return SchoolResource[] Returns an array of SchoolResource objects
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
    public function findOneBySomeField($value): ?SchoolResource
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
