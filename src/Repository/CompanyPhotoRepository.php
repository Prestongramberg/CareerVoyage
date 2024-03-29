<?php

namespace App\Repository;

use App\Entity\CompanyPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyPhoto[]    findAll()
 * @method CompanyPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyPhoto::class);
    }

    // /**
    //  * @return CompanyImage[] Returns an array of CompanyImage objects
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
    public function findOneBySomeField($value): ?CompanyImage
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
