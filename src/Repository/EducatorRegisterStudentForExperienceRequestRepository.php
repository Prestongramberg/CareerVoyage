<?php

namespace App\Repository;

use App\Entity\CompanyExperience;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\Experience;
use App\Entity\StudentUser;
use App\Entity\EducatorUser;
use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EducatorRegisterStudentForCompanyExperienceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorRegisterStudentForCompanyExperienceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorRegisterStudentForCompanyExperienceRequest[]    findAll()
 * @method EducatorRegisterStudentForCompanyExperienceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorRegisterStudentForExperienceRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducatorRegisterStudentForCompanyExperienceRequest::class);
    }

    // /**
    //  * @return StudentRegisterForCompanyExperienceRequest[] Returns an array of StudentRegisterForCompanyExperienceRequest objects
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
    public function findOneBySomeField($value): ?StudentRegisterForCompanyExperienceRequest
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
     * @param StudentUser $student
     * @param CompanyExperience $experience
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByStudentAndExperience(StudentUser $student, CompanyExperience $experience) {
        return $this->createQueryBuilder('e')
            ->where('e.studentUser = :student_id')
            ->andWhere('e.companyExperience = :company_experience_id')
            ->setParameter('student_id', $student->getId())
            ->setParameter('company_experience_id', $experience->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function getUnreadStudentCompanyRequests(StudentUser $student) {
        return $this->createQueryBuilder('e')
            ->where('e.studentUser = :student')
            ->andWhere('e.studentHasSeen = :denied')
            ->setParameter('student', $student)
            ->setParameter('denied', false)
            ->getQuery()
            ->getResult();
    }

    

    public function getUnreadEducatorCompanyRequests(EducatorUser $educator) {
        $user_id = $educator->getId();
        $query = sprintf('SELECT * FROM educator_register_student_for_company_experience_request e, request r 
                          WHERE e.id=r.id 
                          AND r.educator_has_seen = false
                          AND r.created_by_id = '.$user_id);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
