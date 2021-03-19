<?php

namespace App\Repository;

use App\Entity\AllowedCommunication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AllowedCommunication|null find($id, $lockMode = null, $lockVersion = null)
 * @method AllowedCommunication|null findOneBy(array $criteria, array $orderBy = null)
 * @method AllowedCommunication[]    findAll()
 * @method AllowedCommunication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllowedCommunicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AllowedCommunication::class);
    }

    // /**
    //  * @return AllowedCommunication[] Returns an array of AllowedCommunication objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AllowedCommunication
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
