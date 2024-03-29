<?php

namespace App\Repository;

use App\Entity\EducatorVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EducatorVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorVideo[]    findAll()
 * @method EducatorVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducatorVideo::class);
    }

    // /**
    //  * @return EducatorVideo[] Returns an array of EducatorVideo objects
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
    public function findOneBySomeField($value): ?EducatorVideo
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
