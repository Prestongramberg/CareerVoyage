<?php

namespace App\Repository;

use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EducatorReviewTeachLessonExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorReviewTeachLessonExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorReviewTeachLessonExperienceFeedback[]    findAll()
 * @method EducatorReviewTeachLessonExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorReviewTeachLessonExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EducatorReviewTeachLessonExperienceFeedback::class);
    }

    // /**
    //  * @return EducatorReviewTeachLessonExperienceFeedback[] Returns an array of EducatorReviewTeachLessonExperienceFeedback objects
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
    public function findOneBySomeField($value): ?EducatorReviewTeachLessonExperienceFeedback
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
