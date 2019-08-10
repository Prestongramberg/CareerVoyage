<?php

namespace App\Repository;

use App\Entity\SchoolAdministrator;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolAdministrator|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolAdministrator|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolAdministrator[]    findAll()
 * @method SchoolAdministrator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolAdministratorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolAdministrator::class);
    }

    // /**
    //  * @return SchoolAdministrator[] Returns an array of SchoolAdministrator objects
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
    public function findOneBySomeField($value): ?SchoolAdministrator
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
