<?php

namespace App\Repository;

use App\Entity\StudentReviewMeetProfessionalExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StudentReviewMeetProfessionalExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentReviewMeetProfessionalExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentReviewMeetProfessionalExperienceFeedback[]    findAll()
 * @method StudentReviewMeetProfessionalExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentReviewMeetProfessionalExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StudentReviewMeetProfessionalExperienceFeedback::class);
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
