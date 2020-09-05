<?php

namespace App\Repository;

use App\Entity\ProfessionalReviewSchoolExperienceFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProfessionalReviewSchoolExperienceFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfessionalReviewSchoolExperienceFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfessionalReviewSchoolExperienceFeedback[]    findAll()
 * @method ProfessionalReviewSchoolExperienceFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfessionalReviewSchoolExperienceFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProfessionalReviewSchoolExperienceFeedback::class);
    }

    // /**
    //  * @return ProfessionalReviewExperienceFeedback[] Returns an array of ProfessionalReviewExperienceFeedback objects
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
    public function findOneBySomeField($value): ?ProfessionalReviewExperienceFeedback
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
