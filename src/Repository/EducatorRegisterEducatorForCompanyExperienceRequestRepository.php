<?php

namespace App\Repository;

use App\Entity\CompanyExperience;
use App\Entity\EducatorRegisterEducatorForCompanyExperienceRequest;
use App\Entity\Experience;
use App\Entity\EducatorUser;
use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EducatorRegisterEducatorForCompanyExperienceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorRegisterEducatorForCompanyExperienceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorRegisterEducatorForCompanyExperienceRequest[]    findAll()
 * @method EducatorRegisterEducatorForCompanyExperienceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorRegisterEducatorForCompanyExperienceRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducatorRegisterEducatorForCompanyExperienceRequest::class);
    }

    // /**
    //  * @return EducatorRegisterEducatorForCompanyExperienceRequest[] Returns an array of EducatorRegisterEducatorForCompanyExperienceRequest objects
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
     * @param EducatorUser $educator
     * @param CompanyExperience $experience
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByEducatorAndExperience(EducatorUser $educator, CompanyExperience $experience) {
        return $this->createQueryBuilder('e')
            ->where('e.educatorUser = :educator_id')
            ->andWhere('e.companyExperience = :company_experience_id')
            ->setParameter('educator_id', $educator->getId())
            ->setParameter('company_experience_id', $experience->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
