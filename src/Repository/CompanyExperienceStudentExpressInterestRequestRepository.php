<?php

namespace App\Repository;

use App\Entity\CompanyExperienceStudentExpressInterestRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompanyExperienceStudentExpressInterestRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyExperienceStudentExpressInterestRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyExperienceStudentExpressInterestRequest[]    findAll()
 * @method CompanyExperienceStudentExpressInterestRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyExperienceStudentExpressInterestRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompanyExperienceStudentExpressInterestRequest::class);
    }

    // /**
    //  * @return CompanyExperienceStudentExpressInterestRequest[] Returns an array of CompanyExperienceStudentExpressInterestRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompanyExperienceStudentExpressInterestRequest
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
