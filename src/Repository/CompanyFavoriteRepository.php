<?php

namespace App\Repository;

use App\Entity\CompanyFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyFavorite[]    findAll()
 * @method CompanyFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyFavorite::class);
    }

    // /**
    //  * @return CompanyFavorite[] Returns an array of CompanyFavorite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompanyFavorite
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
