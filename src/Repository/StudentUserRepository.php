<?php

namespace App\Repository;

use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Model\GlobalShareFilters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StudentUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentUser[]    findAll()
 * @method StudentUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StudentUser::class);
    }

    // /**
    //  * @return StudentUser[] Returns an array of StudentUser objects
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
    public function findOneBySomeField($value): ?StudentUser
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

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_STUDENT_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join student_user su on u.id = su.id where su.school_id = "%s" and CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"',
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

        $query = 'SELECT u.id, u.first_name, u.last_name, "ROLE_STUDENT_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join student_user su on u.id = su.id';

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

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_STUDENT_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u 
                 inner join student_user su on u.id = su.id
                 INNER JOIN school s on su.school_id = s.id
            where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%" AND s.region_id IN ('. implode(",", $regionIds) . ')', $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getStudentsForRegion(Region $region) {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.site', 'site')
            ->innerJoin('site.regions', 'regions')
            ->andWhere('regions = :region')
            ->setParameter('region', $region)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $search
     * @param ProfessionalUser $professionalUser
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByAllowedCommunication($search, ProfessionalUser $professionalUser) {

        $query = sprintf('SELECT DISTINCT u.id, u.first_name, u.last_name, "ROLE_STUDENT_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u 
        inner join student_user su on u.id = su.id inner join allowed_communication ac on su.id = ac.student_user_id 
        where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%" and ac.professional_user_id = "%s"', $search, $professionalUser->getId());

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function findStudentBySecondaryIndustry($secondaryIndustry) {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.secondaryIndustries', 's')
            ->andWhere('s.id = :secondaryIndustry')
            ->setParameter('secondaryIndustry', $secondaryIndustry)
            ->getQuery()
            ->getResult();
    }

    public function findStudentByGraduatingYear($graduatingYear) {
        return $this->createQueryBuilder('su')
            ->andWhere('su.graduatingYear = :graduatingYear')
            ->andWhere('su.archived = :archived')
            ->setParameter('graduatingYear', $graduatingYear)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array              $userIds
     * @param GlobalShareFilters $filters
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDataForGlobalShare(array $userIds, GlobalShareFilters $filters = null) {

        $ids = implode("','", $userIds);

        $query = "SELECT u.id, CONCAT(\"/media/cache/squared_thumbnail_small/uploads/profile_photo/\", u.photo) as photoImageURL, 'student' as user_role, u.first_name, u.last_name, u.email, u.username, s.id as school_id, 
			s.name as school_name, su.career_statement as interests,
			si.id as secondary_industry_id, si.name as secondary_industry_name,
			i.id as primary_industry_id, i.name as primary_industry_name FROM user u
          INNER JOIN student_user su ON u.id = su.id
          LEFT JOIN school s on s.id = su.school_id
          LEFT JOIN student_user_secondary_industry susi on susi.student_user_id = su.id
          LEFT JOIN secondary_industry si on susi.secondary_industry_id = si.id
          LEFT JOIN industry i on si.primary_industry_id = i.id
          WHERE u.id IN('$ids')";

        if($filters) {

            if(!empty($filters->getInterestSearch())) {
                $query .= sprintf(' AND su.career_statement LIKE "%%%s%%"', $filters->getInterestSearch());
            }

            if(!empty($filters->getPrimaryIndustries())) {
                $primaryIndustries = implode("','", $filters->getPrimaryIndustries());
                $query .= " AND si.primary_industry_id IN('$primaryIndustries')";
            }

            if(!empty($filters->getSecondaryIndustries())) {
                $secondaryIndustries = implode("','", $filters->getSecondaryIndustries());
                $query .= " AND susi.secondary_industry_id IN('$secondaryIndustries')";
            }

            if(!empty($filters->getSchools())) {
                $schools = implode("','", $filters->getSchools());
                $query .= " AND s.id IN('$schools')";
            }

        }

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
