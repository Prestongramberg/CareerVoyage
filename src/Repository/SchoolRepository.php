<?php

namespace App\Repository;

use App\Entity\School;
use App\Entity\SchoolAdministrator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method School|null find($id, $lockMode = null, $lockVersion = null)
 * @method School|null findOneBy(array $criteria, array $orderBy = null)
 * @method School[]    findAll()
 * @method School[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, School::class);
    }


    public function findAll() {
        return $this->findBy(array(), array("name" => "ASC"));
    }


    public function createAlphabeticalSearch()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC');
    }

    // /**
    //  * @return School[] Returns an array of School objects
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
    public function findOneBySomeField($value): ?School
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getSchoolsThatBelongToSchoolAdministrator(SchoolAdministrator $schoolAdministrator) {

        return $this->createQueryBuilder('school')
            ->join('school.schoolAdministrators', 'school_administrators')
            ->where('school_administrators.id = :schoolAdministratorId')
            ->setParameter('schoolAdministratorId', $schoolAdministrator->getId())
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
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findByRadius($latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude) {

        $query = sprintf('SELECT id from school s WHERE s.latitude <= %s AND s.latitude >= %s AND s.longitude <= %s AND s.longitude >= %s AND (s.latitude != %s AND s.longitude != %s)',
            $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
        );

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $schoolIds
     * @return mixed
     */
    public function getByArrayOfIds($schoolIds) {

        return $this->createQueryBuilder('s')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $schoolIds)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
