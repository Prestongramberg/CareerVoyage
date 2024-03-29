<?php

namespace App\Repository;

use App\Entity\TeachLessonExperience;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeachLessonExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeachLessonExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeachLessonExperience[]    findAll()
 * @method TeachLessonExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeachLessonExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function getCompletedByUser(User $user) {
        return $this->createQueryBuilder('e')
            ->where('e.teacher = :teacher')
            ->andWhere('e.startDateAndTime <= :startDateAndTime')
            ->setParameter('teacher', $user)
            ->setParameter('startDateAndTime' , new \DateTime())
            ->getQuery()
            ->getResult();
    }
}
