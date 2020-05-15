<?php

namespace App\Repository;

use App\Entity\ProfessionalReviewMeetStudentExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProfessionalReviewMeetStudentExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfessionalReviewMeetStudentExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfessionalReviewMeetStudentExperienceFeedback[]    findAll()
 * @method ProfessionalReviewMeetStudentExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfessionalReviewMeetStudentExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProfessionalReviewMeetStudentExperienceFeedback::class);
    }

    // /**
    //  * @return ProfessionalReviewMeetStudentExperienceFeedback[] Returns an array of ProfessionalReviewMeetStudentExperienceFeedback objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfessionalReviewMeetStudentExperienceFeedback
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
