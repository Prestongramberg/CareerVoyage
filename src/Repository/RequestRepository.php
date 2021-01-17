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


    // Functions used to calculate the notification count
    // //////////////////////////////////////////////////

    // Students
        public function getUnreadMyRequestsStudent($user) {
            return $this->createQueryBuilder('r')
                ->where('r.created_by = :user')
                ->andWhere('r.studentHasSeen = :false')
                ->setParameter('user', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }

        public function getUnreadErsfceStudent($user) {
            return $this->createQueryBuilder('r')
                ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
                ->andWhere('e.studentUser = :user')
                ->andWhere('r.studentHasSeen = :false')
                ->setParameter('user', $user)
                ->setParameter('false', false)
                ->groupBy('e.id')
                ->getQuery()
                ->getResult();
        }

        public function getUnreadApprovalsByMeStudent($user) {
            return $this->createQueryBuilder('r')
                ->andWhere('r.needsApprovalBy = :user')
                ->andWhere('r.studentHasSeen = :false')
                ->setParameter('user', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }

        public function getUndreadSchoolExperiencesStudent($user) {
            return $this->createQueryBuilder('r')
            ->leftJoin('App\Entity\UserRegisterForSchoolExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
                ->andWhere('e.user = :user')    
                ->andWhere('r.studentHasSeen = :false')
                ->setParameter('user', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }

    // Educators
        public function getUnreadMyRequestsEducator($user) {
            return $this->createQueryBuilder('r')
                ->where('r.created_by = :user')
                ->andWhere('r.educatorHasSeen = :false')
                ->setParameter('user', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }

        public function getUnreadApprovalsByMeEducator($user) {
            return $this->createQueryBuilder('r')
                ->leftJoin('r.requestPossibleApprovers', 'rpa')
                ->andWhere('r.needsApprovalBy = :needsApprovalBy OR rpa.possibleApprover = :possibleApprover')
                ->andWhere('r.educatorHasSeen = :false')
                ->setParameter('needsApprovalBy', $user)
                ->setParameter('possibleApprover', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }


    // Professionals
        public function getUnreadMyRequestsProfessional($user) {
            return $this->createQueryBuilder('r')
                ->where('r.created_by = :user')
                ->andWhere('r.professionalHasSeen = :false')
                ->setParameter('user', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }

        public function getUnreadApprovalsByMeProfessional($user) {
            return $this->createQueryBuilder('r')
                ->leftJoin('r.requestPossibleApprovers', 'rpa')
                ->andWhere('r.needsApprovalBy = :needsApprovalBy OR rpa.possibleApprover = :possibleApprover')
                ->andWhere('r.professionalHasSeen = :false')
                ->setParameter('needsApprovalBy', $user)
                ->setParameter('possibleApprover', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }


    // School Admin
        public function getUnreadApprovalsByMeSchoolAdmin($user) {
            return $this->createQueryBuilder('r')
                ->leftJoin('r.requestPossibleApprovers', 'rpa')
                ->andWhere('r.needsApprovalBy = :needsApprovalBy OR rpa.possibleApprover = :possibleApprover')
                ->andWhere('r.schoolAdminHasSeen = :false')
                ->setParameter('needsApprovalBy', $user)
                ->setParameter('possibleApprover', $user)
                ->setParameter('false', false)
                ->getQuery()
                ->getResult();
        }

        // $deniedByMeRequests = $this->requestRepository->findBy([
        //     'needsApprovalBy' => $user,
        //     'denied' => true,
        // ], ['createdAt' => 'DESC']);

        // $approvedByMeRequests = $this->requestRepository->findBy([
        //     'needsApprovalBy' => $user,
        //     'approved' => true,
        // ], ['createdAt' => 'DESC']);

}
