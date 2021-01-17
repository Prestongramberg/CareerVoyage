<?php

namespace App\Repository;

use App\Entity\CompanyExperience;
use App\Entity\SchoolAdminRegisterSAForCompanyExperienceRequest;
use App\Entity\Experience;
use App\Entity\SchoolAdministrator;
use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolAdminRegisterSAForCompanyExperienceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolAdminRegisterSAForCompanyExperienceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolAdminRegisterSAForCompanyExperienceRequest[]    findAll()
 * @method SchoolAdminRegisterSAForCompanyExperienceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolAdminRegisterSAForCompanyExperienceRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolAdminRegisterSAForCompanyExperienceRequest::class);
    }

    // /**
    //  * @return SchoolAdminRegisterSAForCompanyExperienceRequest[] Returns an array of SchoolAdminRegisterSAForCompanyExperienceRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EducatorRegisterEducatorForCompanyExperienceRequest
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param SchoolAdministrator $schoolAdmin
     * @param CompanyExperience $experience
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBySchoolAdministratorAndExperience(SchoolAdministrator $schoolAdmin, CompanyExperience $experience) {
        return $this->createQueryBuilder('e')
            ->where('e.schoolAdminUser = :school_admin_id')
            ->andWhere('e.companyExperience = :company_experience_id')
            ->setParameter('school_admin_id', $schoolAdmin->getId())
            ->setParameter('company_experience_id', $experience->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
