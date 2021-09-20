<?php

namespace App\Repository;

use App\Entity\Region;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\SecondaryIndustry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SchoolExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolExperience[]    findAll()
 * @method SchoolExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchoolExperience::class);
    }

    // /**
    //  * @return SchoolExperience[] Returns an array of SchoolExperience objects
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
    public function findOneBySomeField($value): ?SchoolExperience
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
     * @param SecondaryIndustry $secondaryIndustries []
     * @param int               $limit
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
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
    inner join school_experience se on se.id = e.id 
    WHERE e.cancelled = 0 AND e.start_date_and_time >= CURDATE() and (%s) 
    GROUP BY se.id order by e.start_date_and_time ASC LIMIT %s
HERE;

        $query = sprintf($query, $whereClause, $limit);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function getNumberOfEventsGroupedBySchoolForRegion(Region $region)
    {

        $query = <<<HERE
            Select school.id as school_id, school.name as school_name,
            (
            Select count(e.id) from experience e 
            left join school_experience se on se.id = e.id
            left join school s on se.school_id = s.id
            where se.school_id = school.id
            and s.region_id = '%s'
            and MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
            AND YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
            ) as num_of_school_events
            from school 
            
            inner join region r on school.region_id = r.id
            where r.id =  '%s'
            ORDER BY school.name ASC
HERE;

        $query = sprintf($query, $region->getId(), $region->getId());
        $em    = $this->getEntityManager();
        $stmt  = $em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findAllFutureEvents()
    {
        $query = sprintf(
            "select e.id, e.title, e.brief_description from experience e
                inner join school_experience se on se.id = e.id
                WHERE e.end_date_and_time >= DATE(NOW()) AND e.cancelled = 0
                GROUP BY se.id order by e.start_date_and_time ASC"
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
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findAllFromPastDays($days = 7)
    {
        $query = sprintf(
            "select e.id, e.title, e.brief_description from experience e
          inner join school_experience se on se.id = e.id
          WHERE e.created_at >= DATE(NOW()) - INTERVAL %d DAY AND e.cancelled = 0 GROUP BY e.id order by e.created_at DESC", $days
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
     * @param      $latN
     * @param      $latS
     * @param      $lonE
     * @param      $lonW
     * @param      $startingLatitude
     * @param      $startingLongitude
     * @param      $schoolId
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
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function search(
        $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude, $schoolId = null, $startDate = null,
        $endDate = null, $searchQuery = null, $eventType = null, $industry = null, $secondaryIndustry = null
    ) {

        $query = sprintf('SELECT DISTINCT e.id, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, "SchoolExperience" as className from school_experience se INNER JOIN experience e on e.id = se.id 
LEFT JOIN experience_secondary_industry esi on esi.experience_id = e.id
LEFT JOIN secondary_industry si on si.id = esi.secondary_industry_id
LEFT JOIN industry i on i.id = si.primary_industry_id
LEFT JOIN roles_willing_to_fulfill rwtf on e.type_id = rwtf.id
WHERE 1 = 1 AND e.cancelled != %s', 1);

        if ($latN && $latS && $lonE && $lonW && $startingLatitude && $startingLongitude) {
            $query .= sprintf(
                ' AND e.latitude <= %s AND e.latitude >= %s AND e.longitude <= %s AND e.longitude >= %s AND (e.latitude != %s AND e.longitude != %s)',
                $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
            );
        }

        if ($schoolId) {
            $query .= sprintf(' AND se.school_id = %s', $schoolId);
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
}
