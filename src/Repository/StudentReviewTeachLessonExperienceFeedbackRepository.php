<?php

namespace App\Repository;

use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StudentReviewTeachLessonExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentReviewTeachLessonExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentReviewTeachLessonExperienceFeedback[]    findAll()
 * @method StudentReviewTeachLessonExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentReviewTeachLessonExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StudentReviewTeachLessonExperienceFeedback::class);
    }

    // /**
    //  * @return StudentReviewTeachLessonExperienceFeedback[] Returns an array of StudentReviewTeachLessonExperienceFeedback objects
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
    public function findOneBySomeField($value): ?StudentReviewTeachLessonExperienceFeedback
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
