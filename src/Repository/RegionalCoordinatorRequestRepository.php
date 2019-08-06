<?php

namespace App\Repository;

use App\Entity\RegionalCoordinatorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RegionalCoordinatorRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegionalCoordinatorRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegionalCoordinatorRequest[]    findAll()
 * @method RegionalCoordinatorRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionalCoordinatorRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RegionalCoordinatorRequest::class);
    }

    // /**
    //  * @return RegionalCoordinatorRequest[] Returns an array of RegionalCoordinatorRequest objects
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
    public function findOneBySomeField($value): ?RegionalCoordinatorRequest
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
