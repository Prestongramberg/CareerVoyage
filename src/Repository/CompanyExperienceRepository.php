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
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyExperience[]    findAll()
 * @method CompanyExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
     * @param int               $limit
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySecondaryIndustries($secondaryIndustries, $limit = 6)
    {

        $whereClause = [];
        foreach ($secondaryIndustries as $secondaryIndustry) {
            $whereClause[] = sprintf("secondary_industry_id = %s", $secondaryIndustry->getId());
        }

        $whereClause = !empty($whereClause) ? implode(" OR ", $whereClause) : '';

        $query = <<<HERE
    select e.id, e.title, e.brief_description from experience e 
    inner join experience_secondary_industry esi on e.id = esi.experience_id 
    inner join company_experience ce on ce.id = e.id 
    WHERE e.start_date_and_time >= CURDATE() and e.cancelled = 0 and (%s) 
    GROUP BY ce.id order by e.start_date_and_time ASC LIMIT %s
HERE;

        $query = sprintf($query, $whereClause, $limit);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getNumberOfEventsGroupedByPrimaryIndustry()
    {

        $query = <<<HERE
        Select i.id as primary_industry_id, i.name as primary_industry_name,
        (
        Select count(e.id) from experience e where e.id
        IN(SELECT experience_id from experience_secondary_industry esi where secondary_industry_id
        IN(SELECT id from secondary_industry si where si.primary_industry_id = i.id))
        and MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
        and YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
        ) as num_of_company_events
        from industry i
HERE;
        $em    = $this->getEntityManager();
        $stmt  = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getNumberOfRegistrationsGroupedByPrimaryIndustry()
    {
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
        $em    = $this->getEntityManager();
        $stmt  = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getNumberOfRegistrationsGroupedByPrimaryIndustryForRegion(Region $region)
    {
        $query = <<<HERE
        select i.id as primary_industry_id, i.name as primary_industry_name,
        (
        Select count(r.id) from registration r
        inner join experience e on r.experience_id = e.id
        inner join user u on u.id = r.user_id
        left join student_user su on su.id = u.id
        left join educator_user eu on eu.id = u.id
        left join school student_user_school on student_user_school.id = su.school_id
        left join school educator_user_school on educator_user_school.id = eu.school_id
        where r.experience_id IN(
        SELECT id from experience e where e.id 
        IN(SELECT experience_id from experience_secondary_industry esi where secondary_industry_id 
        IN(SELECT id from secondary_industry si where si.primary_industry_id = i.id)))
        and (student_user_school.region_id = %s or educator_user_school.region_id = %s)
        and MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
        and YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
        ) as number_of_registrations
        from industry i
HERE;

        $query = sprintf($query, $region->getId(), $region->getId());
        $em    = $this->getEntityManager();
        $stmt  = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function getNumberOfRegistrationsGroupedByPrimaryIndustryForSchool(School $school)
    {
        $query = <<<HERE
            select i.id as primary_industry_id, i.name as primary_industry_name,
            (
            Select count(r.id) from registration r
            inner join experience e on r.experience_id = e.id
            inner join user u on u.id = r.user_id
            left join student_user su on su.id = u.id
            left join educator_user eu on eu.id = u.id
            left join school student_user_school on student_user_school.id = su.school_id
            left join school educator_user_school on educator_user_school.id = eu.school_id
            where r.experience_id IN(
            SELECT id from experience e where e.id 
            IN(SELECT experience_id from experience_secondary_industry esi where secondary_industry_id 
            IN(SELECT id from secondary_industry si where si.primary_industry_id = i.id)))
            and (student_user_school.id = %s or educator_user_school.id = %s)
            and MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
            and YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
            ) as number_of_registrations
            from industry i
HERE;
        $em    = $this->getEntityManager();
        $stmt  = $em->getConnection()->prepare(
            sprintf(
                $query,
                $school->getId(),
                $school->getId(),
                $school->getId(),
                $school->getId()
            )
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getForSchool(School $school)
    {

        return $this->createQueryBuilder('ce')
                    ->innerJoin('ce.company', 'c')
                    ->innerJoin('c.schools', 's')
                    ->where('s = :school')
                    ->setParameter('school', $school)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findAllFutureEvents()
    {
        $query = sprintf(
            "select e.id, e.title, e.brief_description from experience e
                inner join company_experience ce on ce.id = e.id
                WHERE e.end_date_and_time >= DATE(NOW()) AND e.cancelled = 0
                GROUP BY ce.id order by e.start_date_and_time ASC"
        );

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param int $days
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findAllFromPastDays($days = 7)
    {
        $query = sprintf(
            "select e.id, e.title, e.brief_description from experience e
                inner join company_experience ce on ce.id = e.id
          WHERE e.created_at >= DATE(NOW()) - INTERVAL %d DAY AND e.cancelled = %s GROUP BY e.id order by e.created_at DESC", $days, 0
        );

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * To use this function a few things must happen first
     * 1. You must use google api to find the starting latitude and starting longitude of the starting address or zipcode
     * 2. You must use the geocoder->calculateSearchSquare() service to return the 4 lat/lng points
     * 3. Then you can call this function!
     *
     * @param       $latN
     * @param       $latS
     * @param       $lonE
     * @param       $lonW
     * @param       $startingLatitude
     * @param       $startingLongitude
     * @param null  $startDate
     * @param null  $endDate
     *
     * @param null  $searchQuery
     *
     * @param null  $eventType
     * @param null  $industry
     * @param null  $secondaryIndustry
     *
     * @param array $regionIds
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function search(
        $latN = null, $latS = null, $lonE = null, $lonW = null, $startingLatitude = null, $startingLongitude = null,
        $startDate = null, $endDate = null, $searchQuery = null, $eventType = null, $industry = null, $secondaryIndustry = null, $regionIds = []
    ) {

        $query = sprintf('SELECT DISTINCT e.id, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, DATE_FORMAT(e.start_date_and_time, "%%m/%%d/%%Y %%h:%%i %%p") as friendlyStartDateAndTime, DATE_FORMAT(e.end_date_and_time, "%%m/%%d/%%Y %%h:%%i %%p") as friendlyEndDateAndTime, "CompanyExperience" as className, c.name as companyName, rwtf.event_name as eventType from company_experience ce INNER JOIN experience e on e.id = ce.id 
LEFT JOIN experience_secondary_industry esi on esi.experience_id = e.id
LEFT JOIN secondary_industry si on si.id = esi.secondary_industry_id
LEFT JOIN industry i on i.id = si.primary_industry_id
LEFT JOIN roles_willing_to_fulfill rwtf on e.type_id = rwtf.id
LEFT JOIN company c on ce.company_id = c.id
LEFT JOIN company_region cr on cr.company_id = c.id
LEFT JOIN experience_tag etag on etag.experience_id = e.id
LEFT JOIN tag tag on tag.id = etag.tag_id
WHERE 1 = 1 AND e.cancelled != %s', 1);

        if ($latN && $latS && $lonE && $lonW && $startingLatitude && $startingLongitude) {
            $query .= sprintf(
                ' AND e.latitude <= %s AND e.latitude >= %s AND e.longitude <= %s AND e.longitude >= %s AND (e.latitude != %s AND e.longitude != %s)',
                $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
            );
        }

        if ($startDate && $endDate) {

            $query .= " AND ( ";

            $query .= sprintf(" (DATE(e.start_date_and_time) >= '%s' AND DATE(e.end_date_and_time) <= '%s')", $startDate, $endDate);
            $query .= sprintf(" OR (DATE(e.start_date_and_time) <= '%s' AND DATE(e.end_date_and_time) >= '%s')", $startDate, $endDate);
            $query .= sprintf(" OR ( (DATE(e.start_date_and_time) BETWEEN '%s' AND '%s') OR (DATE(e.end_date_and_time) BETWEEN '%s' AND '%s') )", $startDate, $endDate, $startDate, $endDate);

            $query .= " ) ";
        }

        if($searchQuery) {
            $query .= sprintf(' AND e.title LIKE "%%%s%%"', $searchQuery);
        }


        if($eventType) {
            $query .= sprintf(' AND rwtf.id = %s', $eventType);
        }

        if($industry) {
            $query .= sprintf(' AND (i.id = %s OR tag.primary_industry_id = %s) ', $industry, $industry);
        }

        if($secondaryIndustry) {
            $query .= sprintf(' AND (si.id = %s OR tag.secondary_industry_id = %s)', $secondaryIndustry, $secondaryIndustry);
        }

        if(!empty($regionIds)) {
            //todo need to account for virtual events right here
            $query .= sprintf(' AND (rwtf.name LIKE "%%%s%%" OR cr.region_id IN (%s))', "virtual", implode(",", $regionIds));
        }


        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
