<?php

namespace App\Repository;

use App\Entity\TeachLessonExperience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TeachLessonExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeachLessonExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeachLessonExperience[]    findAll()
 * @method TeachLessonExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeachLessonExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TeachLessonExperience::class);
    }

    // /**
    //  * @return TeachLessonExperience[] Returns an array of TeachLessonExperience objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TeachLessonExperience
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
