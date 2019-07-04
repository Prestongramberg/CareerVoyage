<?php

namespace App\Repository;

use App\Entity\EducatorUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EducatorUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorUser[]    findAll()
 * @method EducatorUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EducatorUser::class);
    }

    // /**
    //  * @return EducatorUser[] Returns an array of EducatorUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EducatorUser
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
