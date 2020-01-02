<?php

namespace App\Repository;

use App\Entity\UserRegisterForSchoolExperienceRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserRegisterForSchoolExperienceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRegisterForSchoolExperienceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRegisterForSchoolExperienceRequest[]    findAll()
 * @method UserRegisterForSchoolExperienceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRegisterForSchoolExperienceRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserRegisterForSchoolExperienceRequest::class);
    }

    // /**
    //  * @return UserRegisterForSchoolExperienceRequest[] Returns an array of UserRegisterForSchoolExperienceRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserRegisterForSchoolExperienceRequest
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
