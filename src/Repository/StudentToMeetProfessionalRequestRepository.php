<?php

namespace App\Repository;

use App\Entity\StudentToMeetProfessionalRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StudentToMeetProfessionalRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentToMeetProfessionalRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentToMeetProfessionalRequest[]    findAll()
 * @method StudentToMeetProfessionalRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentToMeetProfessionalRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StudentToMeetProfessionalRequest::class);
    }

    // /**
    //  * @return StudentToMeetProfessionalRequest[] Returns an array of StudentToMeetProfessionalRequest objects
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
    public function findOneBySomeField($value): ?StudentToMeetProfessionalRequest
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
