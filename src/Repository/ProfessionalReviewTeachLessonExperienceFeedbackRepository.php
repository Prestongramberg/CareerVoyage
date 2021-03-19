<?php

namespace App\Repository;

use App\Entity\ProfessionalReviewTeachLessonExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfessionalReviewTeachLessonExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfessionalReviewTeachLessonExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfessionalReviewTeachLessonExperienceFeedback[]    findAll()
 * @method ProfessionalReviewTeachLessonExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfessionalReviewTeachLessonExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfessionalReviewTeachLessonExperienceFeedback::class);
    }

    // /**
    //  * @return ProfessionalReviewTeachLessonExperienceFeedback[] Returns an array of EducatorReviewTeachLessonExperienceFeedback objects
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
