<?php

namespace App\Repository;

use App\Entity\ExperienceWaver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExperienceWaver|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperienceWaver|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperienceWaver[]    findAll()
 * @method ExperienceWaver[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceWaverRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExperienceWaver::class);
    }

    // /**
    //  * @return ExperienceWaver[] Returns an array of ExperienceWaver objects
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
    public function findOneBySomeField($value): ?ExperienceWaver
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
