<?php

namespace App\Repository;

use App\Entity\LessonFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LessonFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method LessonFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method LessonFavorite[]    findAll()
 * @method LessonFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonFavorite::class);
    }

    // /**
    //  * @return LessonFavorite[] Returns an array of LessonFavorite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LessonFavorite
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
