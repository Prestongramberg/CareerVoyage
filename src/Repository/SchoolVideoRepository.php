<?php

namespace App\Repository;

use App\Entity\SchoolVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolVideo[]    findAll()
 * @method SchoolVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolVideoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolVideo::class);
    }

    // /**
    //  * @return SchoolVideo[] Returns an array of SchoolVideo objects
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
    public function findOneBySomeField($value): ?SchoolVideo
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
