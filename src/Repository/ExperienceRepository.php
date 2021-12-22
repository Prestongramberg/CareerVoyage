<?php

namespace App\Repository;

use App\Entity\Experience;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Experience|null find($id, $lockMode = null, $lockVersion = null)
 * @method Experience|null findOneBy(array $criteria, array $orderBy = null)
 * @method Experience[]    findAll()
 * @method Experience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experience::class);
    }

    // /**
    //  * @return Experience[] Returns an array of Experience objects
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
    public function findOneBySomeField($value): ?Experience
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getAllEventsRegisteredForByUser(User $user)
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.registrations', 'r')
                    ->where('r.user = :user')
                    ->andWhere('e.cancelled = :cancelled')
                    ->setParameter('user', $user)
                    ->setParameter('cancelled', false)
                    ->getQuery()
                    ->getResult();
    }


    public function getEventsBySchool($school)
    {
        // $query = sprintf("select * from experience e
        //   inner join school_experience se on se.id = e.id
        //   inner join feedback f on f.id = e.id
        //   WHERE (e.cancelled IS NULL OR e.cancelled = %d) AND se.school_id = %d GROUP BY e.id", false, $school->getId());

        $query = sprintf(
            "select DISTINCT(e.id), title, e.start_date_and_time from experience e
            inner join school_experience se on se.id = e.id
            inner join feedback f on f.experience_id = e.id
            WHERE (e.cancelled IS NULL OR e.cancelled = %d) AND se.school_id = %d ORDER BY e.start_date_and_time", false, $school->getId()
        );

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function fetchEntitiesBySchool($school)
    {
        // $query = sprintf("select * from experience e
        //   inner join school_experience se on se.id = e.id
        //   inner join feedback f on f.id = e.id
        //   WHERE (e.cancelled IS NULL OR e.cancelled = %d) AND se.school_id = %d GROUP BY e.id", false, $school->getId());

        $query = sprintf(
            "select DISTINCT(e.id), e.start_date_and_time from experience e
            inner join school_experience se on se.id = e.id
            inner join feedback f on f.experience_id = e.id
            WHERE (e.cancelled IS NULL OR e.cancelled = %d) AND se.school_id = %d ORDER BY e.start_date_and_time", false, $school->getId()
        );

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $experienceIds = array_map(
            function ($result) {
                return $result['id'];
            }, $results
        );

        if (!empty($experienceIds)) {
            return $this->findBy(
                [
                    'id' => $experienceIds,
                ]
            );
        }

        return [];
    }

    public function getUpcomingEventsRegisteredForByUser(User $user)
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.registrations', 'r')
                    ->andWhere('r.user = :user')
                    ->andWhere('r.approved = :approved')
                    ->andWhere('e.endDateAndTime >= :today')
                    ->andWhere('e.cancelled = :cancelled')
                    ->setParameter('approved', true)
                    ->setParameter('user', $user)
                    ->setParameter('today', new \DateTime())
                    ->setParameter('cancelled', false)
                    ->orderBy('e.endDateAndTime', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    // todo look into removing this function as I think we can just use the method below instead
    public function getCompletedEventsRegisteredForByUser(User $user)
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.registrations', 'r')
                    ->andWhere('r.user = :user')
                    ->andWhere('r.approved = :approved')
                    ->andWhere('e.endDateAndTime <= :today')
                    ->andWhere('e.cancelled = :cancelled')
                    ->setParameter('approved', true)
                    ->setParameter('user', $user)
                    ->setParameter('today', new \DateTime())
                    ->setParameter('cancelled', false)
                    ->orderBy('e.endDateAndTime', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    public function getCompletedEventsRegisteredForByUserMissingFeedback(User $user)
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.registrations', 'r')
                    ->leftJoin('e.feedback', 'f', "WITH", "f.user = :user")
                    ->andWhere('r.user = :user')
                    ->andWhere('r.approved = :approved')
                    ->andWhere('f.id is NULL')
                    ->andWhere('e.endDateAndTime <= :today')
                    ->andWhere('e.cancelled = :cancelled')
                    ->setParameter('approved', true)
                    ->setParameter('user', $user)
                    ->setParameter('today', new \DateTime())
                    ->setParameter('cancelled', false)
                    ->orderBy('e.endDateAndTime', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    /**
     * To use this function a few things must happen first
     * 1. You must use google api to find the starting latitude and starting longitude of the starting address or zipcode
     * 2. You must use the geocoder->calculateSearchSquare() service to return the 4 lat/lng points
     * 3. Then you can call this function!
     *
     * @param      $latN
     * @param      $latS
     * @param      $lonE
     * @param      $lonW
     * @param      $startingLatitude
     * @param      $startingLongitude
     * @param      $userId
     * @param null $startDate
     * @param null $endDate
     *
     * @param null $searchQuery
     *
     * @param null $eventType
     * @param null $industry
     * @param null $secondaryIndustry
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllEventsRegisteredForByUserByRadius(
        $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude, $userId, $startDate = null, $endDate = null,
        $searchQuery = null, $eventType = null, $industry = null, $secondaryIndustry = null
    ) {

        $query = sprintf(
            'SELECT DISTINCT e.id, r.id as regId, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, CONCAT(UCASE(LEFT(e.discr, 1)), SUBSTRING(e.discr, 2)) as className
             FROM experience e 
             INNER JOIN registration r on r.experience_id = e.id 
             LEFT JOIN experience_secondary_industry esi on esi.experience_id = e.id
             LEFT JOIN secondary_industry si on si.id = esi.secondary_industry_id
             LEFT JOIN industry i on i.id = si.primary_industry_id
             LEFT JOIN roles_willing_to_fulfill rwtf on e.type_id = rwtf.id
             WHERE 1 = 1 AND r.user_id = %s AND e.cancelled = %s', $userId, 0
        );

        // $query = sprintf(
        //     'SELECT DISTINCT e.id, r.id as regId, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, e.discr as className from experience e INNER JOIN registration r on r.experience_id = e.id 
        //      LEFT JOIN experience_secondary_industry esi on esi.experience_id = e.id
        //      LEFT JOIN secondary_industry si on si.id = esi.secondary_industry_id
        //      LEFT JOIN industry i on i.id = si.primary_industry_id
        //      LEFT JOIN roles_willing_to_fulfill rwtf on e.type_id = rwtf.id
        //      WHERE 1 = 1 AND r.user_id = %s AND e.cancelled = %s', $userId, 0
        // );

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

        if ($searchQuery) {
            $query .= sprintf(' AND e.title LIKE "%%%s%%"', $searchQuery);
        }

        if($eventType) {
            $query .= sprintf(' AND rwtf.id = %s', $eventType);
        }

        if($industry) {
            $query .= sprintf(' AND i.id = %s', $industry);
        }

        if($secondaryIndustry) {
            $query .= sprintf(' AND si.id = %s', $secondaryIndustry);
        }

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function getEventsClosestToCurrentDateByArrayOfExperienceIds(array $experienceIds)
    {

        $experienceIds = implode("','", $experienceIds);

        $query = sprintf(
            "SELECT * from experience e where e.end_date_and_time > NOW()
                        AND e.id IN ('$experienceIds')
                        AND e.cancelled = 'false'
                        ORDER BY e.start_date_and_time ASC"
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
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function search(
        $latN = null, $latS = null, $lonE = null, $lonW = null, $startingLatitude = null, $startingLongitude = null,
        $startDate = null, $endDate = null, $searchQuery = null, $eventType = null, $industry = null, $secondaryIndustry = null
    ) {

        $query = 'SELECT * FROM (';


        /************************************ SCHOOL EXPERIENCES ************************************/
        $query .= sprintf('SELECT DISTINCT e.id, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, 
DATE_FORMAT(e.start_date_and_time, "%%m/%%d/%%Y %%h:%%i %%p") as friendlyStartDateAndTime, DATE_FORMAT(e.end_date_and_time, "%%m/%%d/%%Y %%h:%%i %%p") as friendlyEndDateAndTime, 
"SchoolExperience" as className, sc.name as schoolName, null as companyName, rwtf.event_name as eventType, se.school_id as school_id, 
/* ORDER BY UPCOMING EVENTS FIRST */
(CASE WHEN e.start_date_and_time >= CURRENT_DATE() THEN 1 ELSE 0 END) AS ORDER_BY_1, 
/* ORDER BY EVENTS CLOSEST TO THE CURRENT DATE NEXT */
ABS ( DATEDIFF ( e.start_date_and_time, CURRENT_DATE() ) ) AS ORDER_BY_2, 
/* ORDER BY EVENTS THAT HAVE A START DATE IN THE PAST BUT ARE STILL GOING ON (LOT OF PEOPLE ARE DOING THIS FOR REPEATING EVENTS) */
(CASE WHEN e.start_date_and_time < CURRENT_DATE() AND e.end_date_and_time > CURRENT_DATE() THEN 1 ELSE 0 END) AS ORDER_BY_3
from school_experience se INNER JOIN experience e on e.id = se.id 
LEFT JOIN experience_secondary_industry esi on esi.experience_id = e.id
LEFT JOIN secondary_industry si on si.id = esi.secondary_industry_id
LEFT JOIN industry i on i.id = si.primary_industry_id
LEFT JOIN roles_willing_to_fulfill rwtf on e.type_id = rwtf.id
LEFT JOIN school sc on se.school_id = sc.id
LEFT JOIN experience_tag etag on etag.experience_id = e.id
LEFT JOIN tag tag on tag.id = etag.tag_id
WHERE 1 = 1 AND e.cancelled != %s', 1);

        if ($latN && $latS && $lonE && $lonW && $startingLatitude && $startingLongitude) {
            $query .= sprintf(
                ' AND e.latitude <= %s AND e.latitude >= %s AND e.longitude <= %s AND e.longitude >= %s AND (e.latitude != %s AND e.longitude != %s) ',
                $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
            );
        }

        // todo re-check this
        if ($startDate && $endDate) {
            $query .= " AND ( ";
            $query .= sprintf(" (DATE(e.start_date_and_time) >= '%s' AND DATE(e.end_date_and_time) <= '%s') ", $startDate, $endDate);
            $query .= sprintf(" OR (DATE(e.start_date_and_time) <= '%s' AND DATE(e.end_date_and_time) >= '%s') ", $startDate, $endDate);
            $query .= sprintf(" OR ( (DATE(e.start_date_and_time) BETWEEN '%s' AND '%s') OR (DATE(e.end_date_and_time) BETWEEN '%s' AND '%s') ) ", $startDate, $endDate, $startDate, $endDate);
            $query .= " ) ";
        }

        if($searchQuery) {
            $query .= sprintf(' AND e.title LIKE "%%%s%%" ', $searchQuery);
        }

        if($eventType) {
            $query .= sprintf(' AND rwtf.id = %s ', $eventType);
        }

        if($industry) {
            $query .= sprintf(' AND (i.id = %s OR tag.primary_industry_id = %s) ', $industry, $industry);
        }

        if($secondaryIndustry) {
            $query .= sprintf(' AND (si.id = %s OR tag.secondary_industry_id = %s) ', $secondaryIndustry, $secondaryIndustry);
        }

        $query .= ' UNION ALL ';

        /************************************ COMPANY EXPERIENCES ************************************/
        $query .= sprintf('SELECT DISTINCT e.id, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, 
DATE_FORMAT(e.start_date_and_time, "%%m/%%d/%%Y %%h:%%i %%p") as friendlyStartDateAndTime, DATE_FORMAT(e.end_date_and_time, "%%m/%%d/%%Y %%h:%%i %%p") as friendlyEndDateAndTime, 
"CompanyExperience" as className, null as schoolName, c.name as companyName, rwtf.event_name as eventType, null as school_id, 
/* ORDER BY UPCOMING EVENTS FIRST */
(CASE WHEN e.start_date_and_time >= CURRENT_DATE() THEN 1 ELSE 0 END) AS ORDER_BY_1, 
/* ORDER BY EVENTS CLOSEST TO THE CURRENT DATE NEXT */
ABS ( DATEDIFF ( e.start_date_and_time, CURRENT_DATE() ) ) AS ORDER_BY_2, 
/* ORDER BY EVENTS THAT HAVE A START DATE IN THE PAST BUT ARE STILL GOING ON (LOT OF PEOPLE ARE DOING THIS FOR REPEATING EVENTS) */
(CASE WHEN e.start_date_and_time < CURRENT_DATE() AND e.end_date_and_time > CURRENT_DATE() THEN 1 ELSE 0 END) AS ORDER_BY_3
from company_experience ce INNER JOIN experience e on e.id = ce.id 
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
                ' AND e.latitude <= %s AND e.latitude >= %s AND e.longitude <= %s AND e.longitude >= %s AND (e.latitude != %s AND e.longitude != %s) ',
                $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
            );
        }

        // todo re-check this
        if ($startDate && $endDate) {
            $query .= " AND ( ";
            $query .= sprintf(" (DATE(e.start_date_and_time) >= '%s' AND DATE(e.end_date_and_time) <= '%s') ", $startDate, $endDate);
            $query .= sprintf(" OR (DATE(e.start_date_and_time) <= '%s' AND DATE(e.end_date_and_time) >= '%s') ", $startDate, $endDate);
            $query .= sprintf(" OR ( (DATE(e.start_date_and_time) BETWEEN '%s' AND '%s') OR (DATE(e.end_date_and_time) BETWEEN '%s' AND '%s') ) ", $startDate, $endDate, $startDate, $endDate);
            $query .= " ) ";
        }

        if($searchQuery) {
            $query .= sprintf(' AND e.title LIKE "%%%s%%" ', $searchQuery);
        }

        if($eventType) {
            $query .= sprintf(' AND rwtf.id = %s ', $eventType);
        }

        if($industry) {
            $query .= sprintf(' AND (i.id = %s OR tag.primary_industry_id = %s) ', $industry, $industry);
        }

        if($secondaryIndustry) {
            $query .= sprintf(' AND (si.id = %s OR tag.secondary_industry_id = %s) ', $secondaryIndustry, $secondaryIndustry);
        }

        $query .= ' ) a ';

        $query .= ' ORDER BY a.ORDER_BY_1 DESC, a.ORDER_BY_2 ASC, a.ORDER_BY_3 DESC ';

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
