<?php

namespace App\Repository;

use App\Entity\ExperienceFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperienceFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperienceFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperienceFile[]    findAll()
 * @method ExperienceFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperienceFile::class);
    }

    // /**
    //  * @return ExperienceFile[] Returns an array of ExperienceFile objects
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
    public function findOneBySomeField($value): ?ExperienceFile
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
