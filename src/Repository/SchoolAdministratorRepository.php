<?php

namespace App\Repository;

use App\Entity\Region;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolAdministrator|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolAdministrator|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolAdministrator[]    findAll()
 * @method SchoolAdministrator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolAdministratorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolAdministrator::class);
    }

    // /**
    //  * @return SchoolAdministrator[] Returns an array of SchoolAdministrator objects
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
    public function findOneBySomeField($value): ?SchoolAdministrator
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
     * @param string[] $criteria format: array('user' => <user_id>, 'name' => <name>)
     * @return array|object[]
     */
    public function findByUniqueCriteria(array $criteria)
    {
        return $this->_em->getRepository(User::class)->findBy($criteria);
    }

    /**
     * @param $search
     * @param School $school
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySearchTermAndSchool($search, School $school) {

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_SCHOOL_ADMINISTRATOR_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join school_administrator sa on u.id = sa.id inner join school_school_administrator ssa on ssa.school_administrator_id = sa.id inner join school s on ssa.school_id = s.id where s.id = "%s" and CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"',
            $school->getId(), $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $search
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySearchTerm($search) {

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_SCHOOL_ADMINISTRATOR_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join school_administrator sa on u.id = sa.id where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"', $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSchoolAdminsForRegion(Region $region) {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.site', 'site')
            ->innerJoin('site.regions', 'regions')
            ->andWhere('regions = :region')
            ->setParameter('region', $region)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Region $region
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByRegion(Region $region) {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, u.email,
        (
        SELECT GROUP_CONCAT(s.name SEPARATOR \',\') from school s INNER JOIN school_school_administrator ssa WHERE s.id = ssa.school_id
        AND ssa.school_administrator_id = u.id
        ) AS schools
        FROM user u 
        INNER JOIN school_administrator sa on u.id = sa.id 
        INNER JOIN school_school_administrator ssa on sa.id = ssa.school_administrator_id
        INNER JOIN school sc on sc.id = ssa.school_id
        INNER JOIN region r on r.id = sc.region_id
        WHERE r.id = "%s"', $region->getId());

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
    public function fetchAll() {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, sa.phone as phone,
        (
        SELECT GROUP_CONCAT(s.name SEPARATOR \',\') from school s INNER JOIN school_school_administrator ssa WHERE s.id = ssa.school_id
        AND ssa.school_administrator_id = u.id
        ) AS schools
        FROM user u 
        INNER JOIN school_administrator sa on u.id = sa.id 
        INNER JOIN school_school_administrator ssa on sa.id = ssa.school_administrator_id
        INNER JOIN school sc on sc.id = ssa.school_id');

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
