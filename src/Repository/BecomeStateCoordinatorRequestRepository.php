<?php

namespace App\Repository;

use App\Entity\BecomeStateCoordinatorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BecomeStateCoordinatorRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method BecomeStateCoordinatorRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method BecomeStateCoordinatorRequest[]    findAll()
 * @method BecomeStateCoordinatorRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BecomeStateCoordinatorRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BecomeStateCoordinatorRequest::class);
    }

    // /**
    //  * @return BecomeStateCoordinatorRequest[] Returns an array of BecomeStateCoordinatorRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BecomeStateCoordinatorRequest
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
