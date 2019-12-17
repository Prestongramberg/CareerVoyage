<?php

namespace App\Repository;

use App\Entity\StudentToMeetProfessionalExperience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StudentToMeetProfessionalExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentToMeetProfessionalExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentToMeetProfessionalExperience[]    findAll()
 * @method StudentToMeetProfessionalExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentToMeetProfessionalExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StudentToMeetProfessionalExperience::class);
    }

    // /**
    //  * @return StudentToMeetProfessionalExperience[] Returns an array of StudentToMeetProfessionalExperience objects
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
    public function findOneBySomeField($value): ?StudentToMeetProfessionalExperience
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
