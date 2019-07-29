<?php

namespace App\Repository;

use App\Entity\LessonResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LessonResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method LessonResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method LessonResource[]    findAll()
 * @method LessonResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonResourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LessonResource::class);
    }

    // /**
    //  * @return LessonResource[] Returns an array of LessonResource objects
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
    public function findOneBySomeField($value): ?LessonResource
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
