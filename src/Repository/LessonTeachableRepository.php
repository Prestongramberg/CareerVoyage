<?php

namespace App\Repository;

use App\Entity\LessonTeachable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LessonTeachable|null find($id, $lockMode = null, $lockVersion = null)
 * @method LessonTeachable|null findOneBy(array $criteria, array $orderBy = null)
 * @method LessonTeachable[]    findAll()
 * @method LessonTeachable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonTeachableRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LessonTeachable::class);
    }

    // /**
    //  * @return LessonTeachable[] Returns an array of LessonTeachable objects
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
    public function findOneBySomeField($value): ?LessonTeachable
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
