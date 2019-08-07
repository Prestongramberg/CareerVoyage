<?php

namespace App\Repository;

use App\Entity\SchoolExperience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolExperience[]    findAll()
 * @method SchoolExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolExperience::class);
    }

    // /**
    //  * @return SchoolExperience[] Returns an array of SchoolExperience objects
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
    public function findOneBySomeField($value): ?SchoolExperience
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
