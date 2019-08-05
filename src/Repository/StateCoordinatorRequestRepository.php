<?php

namespace App\Repository;

use App\Entity\StateCoordinatorRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StateCoordinatorRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method StateCoordinatorRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method StateCoordinatorRequest[]    findAll()
 * @method StateCoordinatorRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StateCoordinatorRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StateCoordinatorRequest::class);
    }

    // /**
    //  * @return StateCoordinatorRequest[] Returns an array of StateCoordinatorRequest objects
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
    public function findOneBySomeField($value): ?StateCoordinatorRequest
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
