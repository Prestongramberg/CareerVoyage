<?php

namespace App\Repository;

use App\Entity\CompanyExperience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompanyExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyExperience[]    findAll()
 * @method CompanyExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompanyExperience::class);
    }

    // /**
    //  * @return CompanyExperience[] Returns an array of CompanyExperience objects
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
    public function findOneBySomeField($value): ?CompanyExperience
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
