<?php

namespace App\Repository;

use App\Entity\SchoolStateUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolStateUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolStateUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolStateUser[]    findAll()
 * @method SchoolStateUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolStateUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolStateUser::class);
    }

    // /**
    //  * @return SchoolStateUser[] Returns an array of SchoolStateUser objects
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
    public function findOneBySomeField($value): ?SchoolStateUser
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
