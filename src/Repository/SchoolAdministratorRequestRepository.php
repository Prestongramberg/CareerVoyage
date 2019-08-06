<?php

namespace App\Repository;

use App\Entity\SchoolAdministratorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolAdministratorRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolAdministratorRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolAdministratorRequest[]    findAll()
 * @method SchoolAdministratorRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolAdministratorRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolAdministratorRequest::class);
    }

    // /**
    //  * @return SchoolAdministratorRequest[] Returns an array of SchoolAdministratorRequest objects
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
    public function findOneBySomeField($value): ?SchoolAdministratorRequest
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
