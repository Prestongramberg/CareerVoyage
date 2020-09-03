<?php

namespace App\Repository;

use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\StudentUser;
use App\Entity\User;
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


    public function findStudentBySecondaryIndustry(int $secondaryIndustry) {
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
}
