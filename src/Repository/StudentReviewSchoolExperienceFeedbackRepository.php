<?php

namespace App\Repository;

use App\Entity\StudentReviewSchoolExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StudentReviewSchoolExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentReviewSchoolExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentReviewSchoolExperienceFeedback[]    findAll()
 * @method StudentReviewSchoolExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentReviewSchoolExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentReviewSchoolExperienceFeedback::class);
    }

    // /**
    //  * @return StudentReviewExperienceFeedback[] Returns an array of StudentReviewExperienceFeedback objects
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
    public function findOneBySomeField($value): ?StudentReviewExperienceFeedback
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
