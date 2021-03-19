<?php

namespace App\Repository;

use App\Entity\StudentReviewCompanyExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StudentReviewCompanyExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentReviewCompanyExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentReviewCompanyExperienceFeedback[]    findAll()
 * @method StudentReviewCompanyExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentReviewCompanyExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentReviewCompanyExperienceFeedback::class);
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
