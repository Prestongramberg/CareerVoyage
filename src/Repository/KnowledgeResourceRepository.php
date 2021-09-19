<?php

namespace App\Repository;

use App\Entity\KnowledgeResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method KnowledgeResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method KnowledgeResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method KnowledgeResource[]    findAll()
 * @method KnowledgeResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KnowledgeResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KnowledgeResource::class);
    }

    // /**
    //  * @return KnowledgeResource[] Returns an array of KnowledgeResource objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?KnowledgeResource
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
