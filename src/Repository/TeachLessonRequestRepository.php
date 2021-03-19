<?php

namespace App\Repository;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\TeachLessonRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeachLessonRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeachLessonRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeachLessonRequest[]    findAll()
 * @method TeachLessonRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeachLessonRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeachLessonRequest::class);
    }

    // /**
    //  * @return TeachLessonRequest[] Returns an array of TeachLessonRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TeachLessonRequest
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param EducatorUser $educatorUser
     * @param ProfessionalUser $professionalUser
     * @return mixed
     */
    public function getByEducatorAndProfessional(EducatorUser $educatorUser, ProfessionalUser $professionalUser) {

        return $this->createQueryBuilder('teach_lesson_request')
            ->where('teach_lesson_request.created_by = :createdBy')
            ->andWhere('teach_lesson_request.needsApprovalBy = :needsApprovalBy')
            ->setParameter('createdBy', $educatorUser->getId())
            ->setParameter('needsApprovalBy', $professionalUser->getId())
            ->getQuery()
            ->getResult();
    }
}
