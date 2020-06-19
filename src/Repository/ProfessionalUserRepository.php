<?php

namespace App\Repository;

use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\StudentUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProfessionalUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfessionalUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfessionalUser[]    findAll()
 * @method ProfessionalUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfessionalUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProfessionalUser::class);
    }

    // /**
    //  * @return ProfessionalUser[] Returns an array of ProfessionalUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfessionalUser
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param $search
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySearchTerm($search) {

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_PROFESSIONAL_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join professional_user pu on u.id = pu.id where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"', $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $search
     * @param StudentUser $studentUser
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByAllowedCommunication($search, StudentUser $studentUser) {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, "ROLE_PROFESSIONAL_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u 
        inner join professional_user pu on u.id = pu.id inner join allowed_communication ac on pu.id = ac.professional_user_id 
        where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%" and ac.student_user_id = "%s"', $search, $studentUser->getId());

        $em = $this->getEntityManager();
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
     * @param $latN
     * @param $latS
     * @param $lonE
     * @param $lonW
     * @param $startingLatitude
     * @param $startingLongitude
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByRadius($latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude) {

        $query = sprintf('SELECT id from professional_user p WHERE p.latitude <= %s AND p.latitude >= %s AND p.longitude <= %s AND p.longitude >= %s AND (p.latitude != %s AND p.longitude != %s)',
            $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
        );

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $professionalIds
     * @return mixed
     */
    public function getByArrayOfIds($professionalIds) {

        return $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $professionalIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchAll() {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, pu.phone, c.name as company,
          IF(c.owner_id = u.id, "YES", "NO") as company_owner,
          CASE WHEN c.owner_id = u.id THEN c.street ELSE NULL END as street,
          CASE WHEN c.owner_id = u.id THEN c.city ELSE NULL END as city,
          CASE WHEN c.owner_id = u.id THEN s.name ELSE NULL END as state,
          CASE WHEN c.owner_id = u.id THEN c.zipcode ELSE NULL END as zipcode
          FROM user u 
          INNER JOIN professional_user pu on u.id = pu.id 
          LEFT JOIN company c on pu.company_id = c.id
          LEFT JOIN state s on c.state_id = s.id');

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    /**
     * @param Region $region
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByRegion(Region $region) {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, pu.phone, c.name as company,
          IF(c.owner_id = u.id, "YES", "NO") as company_owner,
          CASE WHEN c.owner_id = u.id THEN c.street ELSE NULL END as street,
          CASE WHEN c.owner_id = u.id THEN c.city ELSE NULL END as city,
          CASE WHEN c.owner_id = u.id THEN s.name ELSE NULL END as state,
          CASE WHEN c.owner_id = u.id THEN c.zipcode ELSE NULL END as zipcode
          FROM user u 
          INNER JOIN professional_user pu on u.id = pu.id 
          INNER JOIN professional_user_school pus on pus.professional_user_id = u.id
          INNER JOIN school sc on sc.id = pus.school_id
          INNER JOIN region r on r.id = sc.region_id
          LEFT JOIN company c on pu.company_id = c.id
          LEFT JOIN state s on c.state_id = s.id
          WHERE r.id = "%s"', $region->getId());

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
