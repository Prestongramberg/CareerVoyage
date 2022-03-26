<?php

namespace App\Repository;

use App\Entity\UserImportUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserImportUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserImportUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserImportUser[]    findAll()
 * @method UserImportUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserImportUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserImportUser::class);
    }

    // /**
    //  * @return UserImportUser[] Returns an array of UserImportUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserImportUser
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
