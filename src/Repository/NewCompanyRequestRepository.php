<?php

namespace App\Repository;

use App\Entity\NewCompanyRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NewCompanyRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewCompanyRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewCompanyRequest[]    findAll()
 * @method NewCompanyRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewCompanyRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NewCompanyRequest::class);
    }

    // /**
    //  * @return NewCompanyRequest[] Returns an array of NewCompanyRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NewCompanyRequest
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
