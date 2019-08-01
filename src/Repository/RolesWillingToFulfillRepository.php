<?php

namespace App\Repository;

use App\Entity\RolesWillingToFulfill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RolesWillingToFulfill|null find($id, $lockMode = null, $lockVersion = null)
 * @method RolesWillingToFulfill|null findOneBy(array $criteria, array $orderBy = null)
 * @method RolesWillingToFulfill[]    findAll()
 * @method RolesWillingToFulfill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolesWillingToFulfillRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RolesWillingToFulfill::class);
    }

    // /**
    //  * @return RolesWillingToFulfill[] Returns an array of RolesWillingToFulfill objects
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
    public function findOneBySomeField($value): ?RolesWillingToFulfill
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
