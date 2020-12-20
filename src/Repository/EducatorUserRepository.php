<?php

namespace App\Repository;

use App\Entity\EducatorUser;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\Course;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method EducatorUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducatorUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducatorUser[]    findAll()
 * @method EducatorUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducatorUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EducatorUser::class);
    }

    // /**
    //  * @return EducatorUser[] Returns an array of EducatorUser objects
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
    public function findOneBySomeField($value): ?EducatorUser
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
     * @param string[] $criteria format: array('user' => <user_id>, 'name' => <name>)
     * @return array|object[]
     */
    public function findByUniqueCriteria(array $criteria)
    {
        return $this->_em->getRepository(User::class)->findBy($criteria);
    }

    /**
     * @param SecondaryIndustry $secondaryIndustries[]
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySecondaryIndustries($secondaryIndustries) {

        $whereClause = [];
        foreach($secondaryIndustries as $secondaryIndustry) {
            $whereClause[] = sprintf("secondary_industry_id = %s", $secondaryIndustry->getId());
        }

        $query = sprintf("select eu.id, u.first_name, u.last_name, u.email from educator_user eu inner join educator_user_secondary_industry si on eu.id = si.educator_user_id inner join user u on eu.id = u.id WHERE %s GROUP BY eu.id", implode(" OR ", $whereClause));

        $em = $this->getEntityManager();
        $stmt = $em->
        getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $search
     * @param School $school
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySearchTermAndSchool($search, School $school) {

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_EDUCATOR_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join educator_user eu on u.id = eu.id where eu.school_id = "%s" and CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"',
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

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_EDUCATOR_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join educator_user eu on u.id = eu.id where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"', $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param        $search
     * @param array  $regionIds
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySearchTermAndRegionIds($search, array $regionIds) {

        if(empty($regionIds)) {
            return [];
        }

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_EDUCATOR_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u 
        inner join educator_user eu on u.id = eu.id 
        INNER JOIN school s on eu.school_id = s.id
        where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%" AND s.region_id IN ('. implode(",", $regionIds) . ')', $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEducatorsForRegion(Region $region) {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.site', 'site')
            ->innerJoin('site.regions', 'regions')
            ->andWhere('regions = :region')
            ->setParameter('region', $region)
            ->getQuery()
            ->getResult();
    }

    public function findByFavoriteLessonIds($lessonIds) {
        return $this->createQueryBuilder('eu')
            ->innerJoin('eu.lessonFavorites','lf')
            ->where("lf.lesson IN(:lessonIds)")
            ->setParameter('lessonIds', $lessonIds)
            ->getQuery()
            ->getResult();
    }

    public function findByArrayOfSchoolIds($schoolIds) {
        return $this->createQueryBuilder('e')
            ->where("e.school IN (:schools)")
            ->setParameter('schools', $schoolIds)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param $educatorIds
     * @return mixed
     */
    public function getByArrayOfIds($educatorIds) {

        return $this->createQueryBuilder('e')
            ->where('e.id IN (:ids)')
            ->andWhere('e.deleted = 0')
            ->andWhere('e.activated = 1')
            ->setParameter('ids', $educatorIds)
            ->getQuery()
            ->getResult();
    }

    public function getAll() {
        return $this->createQueryBuilder('u')
            ->andWhere('u.deleted = 0')
            ->andWhere('u.activated = 1')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchAll() {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, eu.phone, eu.interests, eu.brief_bio, sc.name as school_name,
          sc.street as street,
          sc.city as city,
          s.name as state,
          sc.zipcode as zipcode
          FROM user u 
          INNER JOIN educator_user eu on u.id = eu.id 
          LEFT JOIN school sc on eu.school_id = sc.id
          LEFT JOIN state s on sc.state_id = s.id
          AND u.deleted = 0');

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findEducatorBySecondaryIndustry($secondaryIndustry) {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.secondaryIndustries', 's')
            ->andWhere('s.id = :secondaryIndustry')
            ->setParameter('secondaryIndustry', $secondaryIndustry)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $userIds
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDataForGlobalShare(array $userIds) {

        $ids = implode("','", $userIds);

        $query = "SELECT u.id, CONCAT(\"/media/cache/squared_thumbnail_small/uploads/profile_photo/\", u.photo) as photoImageURL, 'educator' as user_role, u.first_name, u.last_name, u.email, s.id as school_id, 
			s.name as school_name, eu.interests, c.id as course_id, c.title as course_title,
			si.id as secondary_industry_id, si.name as secondary_industry_name,
			i.id as primary_industry_id, i.name as primary_industry_name FROM user u
          INNER JOIN educator_user eu ON u.id = eu.id
          LEFT JOIN school s on s.id = eu.school_id
          LEFT JOIN educator_user_secondary_industry eusi on eusi.educator_user_id = eu.id
          LEFT JOIN secondary_industry si on eusi.secondary_industry_id = si.id
          LEFT JOIN industry i on si.primary_industry_id = i.id
          LEFT JOIN educator_user_course euc on euc.educator_user_id = eu.id
          LEFT JOIN course c on euc.course_id = c.id
          WHERE u.id IN('$ids')";


        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
