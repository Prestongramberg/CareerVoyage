<?php

namespace App\Repository;

use App\Entity\SchoolPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SchoolPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolPhoto[]    findAll()
 * @method SchoolPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchoolPhoto::class);
    }

    // /**
    //  * @return SchoolPhoto[] Returns an array of SchoolPhoto objects
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
    public function findOneBySomeField($value): ?SchoolPhoto
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
