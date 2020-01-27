<?php

namespace App\Repository;

use App\Entity\Request;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Request|null find($id, $lockMode = null, $lockVersion = null)
 * @method Request|null findOneBy(array $criteria, array $orderBy = null)
 * @method Request[]    findAll()
 * @method Request[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Request::class);
    }

    // /**
    //  * @return Request[] Returns an array of Request objects
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
    public function findOneBySomeField($value): ?Request
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getRequestsThatNeedMyApproval(User $user) {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.requestPossibleApprovers', 'rpa')
            ->andWhere('r.needsApprovalBy = :needsApprovalBy OR rpa.possibleApprover = :possibleApprover')
            ->andWhere('r.denied = :denied')
            ->andWhere('r.approved = :approved')
            ->andWhere('r.allowApprovalByActivationCode = :allowApprovalByActivationCode')
            ->setParameter('possibleApprover', $user)
            ->setParameter('needsApprovalBy', $user)
            ->setParameter('denied', false)
            ->setParameter('approved', false)
            ->setParameter('allowApprovalByActivationCode', false)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
