<?php

namespace App\Repository;

use App\Entity\EducatorReviewCompanyExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EducatorReviewCompanyExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorReviewCompanyExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorReviewCompanyExperienceFeedback[]    findAll()
 * @method EducatorReviewCompanyExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorReviewCompanyExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducatorReviewCompanyExperienceFeedback::class);
    }

    // /**
    //  * @return EducatorReviewCompanyExperienceFeedback[] Returns an array of EducatorReviewCompanyExperienceFeedback objects
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
    public function findOneBySomeField($value): ?EducatorReviewCompanyExperienceFeedback
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
