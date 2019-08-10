<?php

namespace App\Repository;

use App\Entity\StateCoordinator;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StateCoordinator|null find($id, $lockMode = null, $lockVersion = null)
 * @method StateCoordinator|null findOneBy(array $criteria, array $orderBy = null)
 * @method StateCoordinator[]    findAll()
 * @method StateCoordinator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StateCoordinatorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StateCoordinator::class);
    }

    // /**
    //  * @return StateCoordinator[] Returns an array of StateCoordinator objects
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
    public function findOneBySomeField($value): ?StateCoordinator
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
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
