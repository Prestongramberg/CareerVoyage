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
            ->setParameter('ids', $educatorIds)
            ->getQuery()
            ->getResult();
    }

    public function getAll() {
        return $this->createQueryBuilder('u')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchAll() {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, eu.phone, sc.name, eu.brief_bio as school_name,
          sc.street as street,
          sc.city as city,
          s.name as state,
          sc.zipcode as zipcode
          FROM user u 
          INNER JOIN educator_user eu on u.id = eu.id 
          LEFT JOIN school sc on eu.school_id = sc.id
          LEFT JOIN state s on sc.state_id = s.id');

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findEducatorBySecondaryIndustry(int $secondaryIndustry) {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.secondaryIndustries', 's')
            ->andWhere('s.id = :secondaryIndustry')
            ->setParameter('secondaryIndustry', $secondaryIndustry)
            ->getQuery()
            ->getResult();
    }
}
