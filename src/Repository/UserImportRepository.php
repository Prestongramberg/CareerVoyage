<?php

namespace App\Repository;

use App\Entity\UserImport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserImport|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserImport|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserImport[]    findAll()
 * @method UserImport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserImportRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserImport::class);
    }

    // /**
    //  * @return UserImport[] Returns an array of UserImport objects
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
    public function findOneBySomeField($value): ?UserImport
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findBySchoolIds($schoolIds)
    {
        return $this->createQueryBuilder('u')
                    ->innerJoin('u.school', 's')
                    ->innerJoin('u.userImportUsers', 'uiu')
                    ->andWhere('s.id IN (:schoolIds)')
                    ->andWhere('uiu.user IS NOT NULL')
                    ->setParameter('schoolIds', $schoolIds)
                    ->getQuery()
                    ->getResult();
    }
}
