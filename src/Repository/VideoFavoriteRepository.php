<?php

namespace App\Repository;

use App\Entity\VideoFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoFavorite[]    findAll()
 * @method VideoFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VideoFavorite::class);
    }

    // /**
    //  * @return VideoFavorite[] Returns an array of VideoFavorite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VideoFavorite
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}