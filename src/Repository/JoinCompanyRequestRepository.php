<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\JoinCompanyRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JoinCompanyRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method JoinCompanyRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method JoinCompanyRequest[]    findAll()
 * @method JoinCompanyRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JoinCompanyRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JoinCompanyRequest::class);
    }

    // /**
    //  * @return JoinCompanyRequest[] Returns an array of JoinCompanyRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JoinCompanyRequest
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param Company $company
     * @param User $user
     * @return mixed
     */
    public function getJoinCompanyRequestsByCompanyAndUser(Company $company, User $user) {

        return $this->createQueryBuilder('joinCompanyRequest')
            ->join('joinCompanyRequest.company', 'c')
            ->where('c.id = :companyID')
            ->andWhere('joinCompanyRequest.created_by = :user')
            ->setParameter('companyID', $company->getId())
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getJoinCompanyRequestsFromCompanyByUser(User $user) {

        return $this->createQueryBuilder('joinCompanyRequest')
            ->where('joinCompanyRequest.needsApprovalBy = :user')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult();
    }
}
