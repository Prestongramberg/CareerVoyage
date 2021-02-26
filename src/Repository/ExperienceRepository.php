<?php

namespace App\Repository;

use App\Entity\Experience;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Experience|null find($id, $lockMode = null, $lockVersion = null)
 * @method Experience|null findOneBy(array $criteria, array $orderBy = null)
 * @method Experience[]    findAll()
 * @method Experience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

    public function getUpcomingEventsRegisteredForByUser(User $user)
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.registrations', 'r')
                    ->where('r.user = :user')
                    ->andWhere('e.startDateAndTime >= :startDateAndTime')
                    ->andWhere('e.cancelled = :cancelled')
                    ->setParameter('user', $user)
                    ->setParameter('startDateAndTime', new \DateTime())
                    ->setParameter('cancelled', false)
                    ->getQuery()
                    ->getResult();
    }

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

    public function getCompletedEventsRegisteredForByUser(User $user)
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.registrations', 'r')
                    ->where('r.user = :user')
                    ->andWhere('e.startDateAndTime <= :startDateAndTime')
                    ->andWhere('e.cancelled = :cancelled')
                    ->setParameter('user', $user)
                    ->setParameter('startDateAndTime', new \DateTime())
                    ->setParameter('cancelled', false)
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

        // $query = sprintf(
        //     'SELECT DISTINCT e.id, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, "Experience" as className from experience e INNER JOIN registration r on r.experience_id = e.id 
        //     LEFT JOIN experience_secondary_industry esi on esi.experience_id = e.id
        //     LEFT JOIN secondary_industry si on si.id = esi.secondary_industry_id
        //     LEFT JOIN industry i on i.id = si.primary_industry_id
        //     LEFT JOIN roles_willing_to_fulfill rwtf on e.type_id = rwtf.id
        //     WHERE 1 = 1 AND r.user_id = %s AND e.cancelled = %s', $userId, 0
        // );

        $query = sprintf(
            'SELECT DISTINCT e.id, r.id as regId, e.title, e.about, e.brief_description as briefDescription, e.start_date_and_time as startDateAndTime, e.end_date_and_time as endDateAndTime, e.discr as className from experience e INNER JOIN registration r on r.experience_id = e.id 
             LEFT JOIN experience_secondary_industry esi on esi.experience_id = e.id
             LEFT JOIN secondary_industry si on si.id = esi.secondary_industry_id
             LEFT JOIN industry i on i.id = si.primary_industry_id
             LEFT JOIN roles_willing_to_fulfill rwtf on e.type_id = rwtf.id
             WHERE 1 = 1 AND r.user_id = %s AND e.cancelled = %s', $userId, 0
        );

        if ($latN && $latS && $lonE && $lonW && $startingLatitude && $startingLongitude) {
            $query .= sprintf(
                ' AND e.latitude <= %s AND e.latitude >= %s AND e.longitude <= %s AND e.longitude >= %s AND (e.latitude != %s AND e.longitude != %s)',
                $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
            );
        }

        if ($startDate && $endDate) {

            $query .= sprintf(" AND e.start_date_and_time > %s AND e.end_date_and_time < %s", $startDate, $endDate);

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
}
