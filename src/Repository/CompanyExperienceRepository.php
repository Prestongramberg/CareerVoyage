<?php

namespace App\Repository;

use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompanyExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyExperience[]    findAll()
 * @method CompanyExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompanyExperience::class);
    }

    // /**
    //  * @return CompanyExperience[] Returns an array of CompanyExperience objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompanyExperience
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param SecondaryIndustry $secondaryIndustries []
     * @param int $limit
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySecondaryIndustries($secondaryIndustries, $limit = 6) {

        $whereClause = [];
        foreach($secondaryIndustries as $secondaryIndustry) {
            $whereClause[] = sprintf("secondary_industry_id = %s", $secondaryIndustry->getId());
        }

        $whereClause = !empty($whereClause) ? implode(" OR ", $whereClause) : '';

        $query = <<<HERE
    select e.id, e.title, e.brief_description from experience e 
    inner join experience_secondary_industry esi on e.id = esi.experience_id 
    inner join company_experience ce on ce.id = e.id 
    WHERE e.start_date_and_time >= CURDATE() and (%s) 
    GROUP BY ce.id order by e.start_date_and_time ASC LIMIT %s
HERE;

        $query = sprintf($query, $whereClause, $limit);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEventsGroupedByPrimaryIndustry() {

        $query = <<<HERE
    select count(ce.id) as num_of_company_events, i.name as primary_industry_name
    from company_experience ce 
    inner join experience e on e.id = ce.id
    inner join experience_secondary_industry esi on esi.experience_id = e.id
    inner join secondary_industry si on esi.secondary_industry_id = si.id
    inner join industry i on i.id = si.primary_industry_id
    where MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
    AND YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
    group by primary_industry_name
HERE;
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getNumberOfRegistrationsGroupedByPrimaryIndustry() {
        $query = <<<HERE
    select DISTINCT i.id as primary_industry_id, i.name as primary_industry_name,  e.id as company_experience_id, 
    (Select count(r.id) from registration r where r.experience_id = e.id) as number_of_registrations, 
    e.title as company_experience_title
    from company_experience ce 
    inner join experience e on e.id = ce.id
    inner join experience_secondary_industry esi on esi.experience_id = e.id
    inner join secondary_industry si on esi.secondary_industry_id = si.id
    inner join industry i on i.id = si.primary_industry_id
    where MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
    AND YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
HERE;
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getNumberOfRegistrationsGroupedByPrimaryIndustryForRegion(Region $region) {
        $query = <<<HERE
    select DISTINCT i.id as primary_industry_id, i.name as primary_industry_name,  e.id as company_experience_id, 
    (
    Select count(r.id) from registration r
    inner join user u on u.id = r.user_id
    left join student_user su on su.id = u.id
    left join educator_user eu on eu.id = u.id
    left join school student_user_school on student_user_school.id = su.school_id
    left join school educator_user_school on educator_user_school.id = eu.school_id
    where r.experience_id = e.id
    and (student_user_school.region_id = %s or educator_user_school.region_id = %s)
    
    ) as number_of_registrations, 
    e.title as company_experience_title
    from company_experience ce 
    inner join experience e on e.id = ce.id
    inner join experience_secondary_industry esi on esi.experience_id = e.id
    inner join secondary_industry si on esi.secondary_industry_id = si.id
    inner join industry i on i.id = si.primary_industry_id
    inner join registration r on r.experience_id = e.id
    inner join user u on u.id = r.user_id
    left join student_user su on su.id = u.id
    left join educator_user eu on eu.id = u.id
    left join school student_user_school on student_user_school.id = su.school_id
    left join school educator_user_school on educator_user_school.id = eu.school_id
    where MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
    AND YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
    AND (student_user_school.region_id = %s or educator_user_school.region_id = %s)
HERE;

        $query = sprintf($query, $region->getId(), $region->getId(), $region->getId(), $region->getId());
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getNumberOfRegistrationsGroupedByPrimaryIndustryInSchool(School $school) {
        $query = <<<HERE
    select DISTINCT i.id as primary_industry_id, i.name as primary_industry_name,  e.id as company_experience_id, 
    (
    Select count(r.id) from registration r 
    inner join user u on u.id = r.user_id
    left join student_user su on su.id = u.id
    left join educator_user eu on eu.id = u.id
    where r.experience_id = e.id
    and (eu.school_id = %s or su.school_id = %s)
    ) as number_of_registrations, 
    e.title as company_experience_title
    from company_experience ce 
    inner join experience e on e.id = ce.id
    inner join registration r on r.experience_id = e.id
    
    inner join user as registered_user on r.user_id = registered_user.id
    left join student_user as registered_student_user on registered_student_user.id = registered_user.id
    left join educator_user as registered_educator_user on registered_educator_user.id = registered_user.id
    
    inner join experience_secondary_industry esi on esi.experience_id = e.id
    inner join secondary_industry si on esi.secondary_industry_id = si.id
    inner join industry i on i.id = si.primary_industry_id
    where MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
    AND YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
    AND registered_educator_user.school_id = %s or registered_student_user.school_id = %s
HERE;
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare(
            sprintf($query,
                $school->getId(),
                $school->getId(),
                $school->getId(),
                $school->getId())
        );

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getForSchool(School $school) {

        return $this->createQueryBuilder('ce')
            ->innerJoin('ce.company', 'c')
            ->innerJoin('c.schools', 's')
            ->where('s = :school')
            ->setParameter('school', $school)
            ->getQuery()
            ->getResult();
    }
}
