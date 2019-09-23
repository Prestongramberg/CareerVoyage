<?php

namespace App\Repository;

use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EducatorRegisterStudentForCompanyExperienceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorRegisterStudentForCompanyExperienceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorRegisterStudentForCompanyExperienceRequest[]    findAll()
 * @method EducatorRegisterStudentForCompanyExperienceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorRegisterStudentForExperienceRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EducatorRegisterStudentForCompanyExperienceRequest::class);
    }

    // /**
    //  * @return StudentRegisterForCompanyExperienceRequest[] Returns an array of StudentRegisterForCompanyExperienceRequest objects
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
    public function findOneBySomeField($value): ?StudentRegisterForCompanyExperienceRequest
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
