<?php

namespace App\Repository;

use App\Entity\ExperienceResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperienceResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperienceResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperienceResource[]    findAll()
 * @method ExperienceResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperienceResource::class);
    }

    // /**
    //  * @return ExperienceResource[] Returns an array of ExperienceResource objects
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
    public function findOneBySomeField($value): ?ExperienceResource
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
