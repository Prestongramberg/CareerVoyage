<?php

namespace App\Repository;

use App\Entity\SecondaryIndustry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SecondaryIndustry|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecondaryIndustry|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecondaryIndustry[]    findAll()
 * @method SecondaryIndustry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecondaryIndustryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SecondaryIndustry::class);
    }

    // /**
    //  * @return SecondaryIndustry[] Returns an array of SecondaryIndustry objects
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
    public function findOneBySomeField($value): ?SecondaryIndustry
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
