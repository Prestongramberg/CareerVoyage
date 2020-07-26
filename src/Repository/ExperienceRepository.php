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

    public function getUpcomingEventsRegisteredForByUser(User $user) {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.registrations', 'r')
            ->where('r.user = :user')
            ->andWhere('e.startDateAndTime >= :startDateAndTime')
            ->setParameter('user', $user)
            ->setParameter('startDateAndTime' , new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function getAllEventsRegisteredForByUser(User $user) {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.registrations', 'r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getCompletedEventsRegisteredForByUser(User $user) {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.registrations', 'r')
            ->where('r.user = :user')
            ->andWhere('e.startDateAndTime <= :startDateAndTime')
            ->setParameter('user', $user)
            ->setParameter('startDateAndTime' , new \DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * To use this function a few things must happen first
     * 1. You must use google api to find the starting latitude and starting longitude of the starting address or zipcode
     * 2. You must use the geocoder->calculateSearchSquare() service to return the 4 lat/lng points
     * 3. Then you can call this function!
     *
     * @param $latN
     * @param $latS
     * @param $lonE
     * @param $lonW
     * @param $startingLatitude
     * @param $startingLongitude
     * @param $userId
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllEventsRegisteredForByUserByRadius($latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude, $userId) {

        $query = sprintf('SELECT * from experience e INNER JOIN registration r on r.experience_id = e.id WHERE e.latitude <= %s AND e.latitude >= %s AND e.longitude <= %s AND e.longitude >= %s AND (e.latitude != %s AND e.longitude != %s) AND r.user_id = %s',
            $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude, $userId
        );

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getEventsClosestToCurrentDateByArrayOfExperienceIds(array $experienceIds) {

        $experienceIds = implode("','", $experienceIds);

        $query = sprintf("SELECT * from experience e where e.start_date_and_time > NOW()
                        AND e.id IN ('$experienceIds')
                        ORDER BY e.start_date_and_time ASC");

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
