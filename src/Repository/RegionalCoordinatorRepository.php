<?php

namespace App\Repository;

use App\Entity\RegionalCoordinator;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegionalCoordinator|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegionalCoordinator|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegionalCoordinator[]    findAll()
 * @method RegionalCoordinator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionalCoordinatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegionalCoordinator::class);
    }

    // /**
    //  * @return RegionalCoordinator[] Returns an array of RegionalCoordinator objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RegionalCoordinator
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param string[] $criteria format: array('user' => <user_id>, 'name' => <name>)
     * @return array|object[]
     */
    public function findByUniqueCriteria(array $criteria)
    {
        return $this->_em->getRepository(User::class)->findBy($criteria);
    }
}
