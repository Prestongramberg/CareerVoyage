<?php

namespace App\Repository;

use App\Entity\CareerVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CareerVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CareerVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CareerVideo[]    findAll()
 * @method CareerVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CareerVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareerVideo::class);
    }

    // /**
    //  * @return CareerVideo[] Returns an array of CareerVideo objects
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
    public function findOneBySomeField($value): ?CareerVideo
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
