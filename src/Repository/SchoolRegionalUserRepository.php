<?php

namespace App\Repository;

use App\Entity\SchoolRegionalUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolRegionalUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolRegionalUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolRegionalUser[]    findAll()
 * @method SchoolRegionalUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolRegionalUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolRegionalUser::class);
    }

    // /**
    //  * @return SchoolRegionalUser[] Returns an array of SchoolRegionalUser objects
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
    public function findOneBySomeField($value): ?SchoolRegionalUser
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
