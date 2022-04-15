<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\School;
use App\Entity\StudentUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

use App\Entity\EducatorUser;


use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * Fetch a user entity by email address
     *
     * @param string $emailAddress
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getByEmailAddress($emailAddress)
    {
        return $this->createQueryBuilder('u')
                    ->where('u.email = :email')
                    ->setParameter('email', $emailAddress)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * Fetch a user entity by invitation code
     *
     * @param $invitationCode
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getByInvitationCode($invitationCode)
    {

        return $this->createQueryBuilder('u')
                    ->where('u.invitationCode = :invitationCode')
                    ->setParameter('invitationCode', $invitationCode)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * Fetch a user entity by activation code
     *
     * @param $activationCode
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getByActivationCode($activationCode)
    {

        return $this->createQueryBuilder('u')
                    ->where('u.activationCode = :activationCode')
                    ->setParameter('activationCode', $activationCode)
                    ->getQuery()
                    ->getOneOrNullResult();
    }


    public function loadUserByUsername($usernameOrEmail)
    {
        return $this->createQueryBuilder('u')
                    ->where('u.username = :query OR u.email = :query')
                    ->setParameter('query', $usernameOrEmail)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * @param $token
     *
     * @return User|null
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function getByPasswordResetToken($token)
    {
        return $this->createQueryBuilder('u')
                    ->where('u.passwordResetToken = :token')
                    ->setParameter('token', $token)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * @param $token
     *
     * @return User|null
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function getByTemporarySecurityToken($token)
    {

        return $this->createQueryBuilder('u')
                    ->where('u.temporarySecurityToken = :token')
                    ->setParameter('token', $token)
                    ->getQuery()
                    ->getOneOrNullResult();
    }


    public function findByRole($role)
    {
        $qb = $this->createQueryBuilder('u')->where('u.roles LIKE :roles')->setParameter('roles', '%"' . $role . '"%');

        return $qb->getQuery()->getResult();
    }


    public function findContactsBySchool(School $school)
    {
        $query = sprintf('SELECT u.id, u.first_name, u.last_name FROM user u
          LEFT JOIN school_school_administrator ssa ON u.id = ssa.school_administrator_id
          LEFT JOIN educator_user eu ON u.id = eu.id
          WHERE ssa.school_id = :school OR eu.school_id = :school
          ORDER BY u.last_name, u.first_name');


        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute(['school' => $school->getId()]);
        $results = $stmt->fetchAll();

        $userIds = array_map(function ($result) { return $result['id']; }, $results);

        if (!empty($userIds)) {
            return $this->findBy([
                'id' => $userIds,
            ]);
        }

        return [];
    }


    /**
     * @param Lesson $lesson
     *
     * @return mixed
     */
    public function getUsersWhoCanTeachLesson(Lesson $lesson)
    {
        return $this->createQueryBuilder('u')
                    ->join('u.lessonTeachables', 'lt')
                    ->join('lt.lesson', 'lesson')
                    ->where('lesson.id = :id')
                    ->setParameter('id', $lesson->getId())
                    ->getQuery()
                    ->getResult();
    }


    /**
     * @param array $schoolIds
     * @param false $queryBuilder
     *
     * @return User[]|\Doctrine\ORM\QueryBuilder
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function search(array $schoolIds = [], bool $queryBuilder = false, $excludeProfessionals = false, $excludeStudents = false)
    {


        $query = 'SELECT * FROM (';

        if (!$excludeProfessionals) {
            /************************************ PROFESSIONAL USERS ************************************/
            $query .= sprintf('SELECT DISTINCT pu.id
from professional_user pu INNER JOIN user u on u.id = pu.id 
WHERE 1 = 1 AND u.deleted != %s', 1);


            $query .= ' UNION ALL ';
        }


        /************************************ EDUCATOR USERS ************************************/
        $query .= sprintf('SELECT DISTINCT eu.id
from educator_user eu INNER JOIN user u on u.id = eu.id 
WHERE 1 = 1 AND u.deleted != %s', 1);


        if (!empty($schoolIds)) {
            $query .= ' AND eu.school_id IN (' . implode(",", $schoolIds) . ')';
        }

        $query .= ' UNION ALL ';

        if (!$excludeStudents) {
            /************************************ STUDENT USERS ************************************/
            $query .= sprintf(
                'SELECT DISTINCT su.id
from student_user su INNER JOIN user u on u.id = su.id 
WHERE 1 = 1 AND u.deleted != %s AND su.archived != %s',
                1,
                1
            );

            if (!empty($schoolIds)) {
                $query .= ' AND su.school_id IN ('.implode(",", $schoolIds).')';
            }

            $query .= ' UNION ALL ';
        }

        /************************************ SCHOOL ADMINISTRATOR USERS ************************************/
        $query .= sprintf('SELECT DISTINCT sa.id
from school_administrator sa INNER JOIN user u on u.id = sa.id
INNER JOIN school_school_administrator ssa on ssa.school_administrator_id = sa.id
WHERE 1 = 1 AND u.deleted != %s', 1);

        if (!empty($schoolIds)) {
            $query .= ' AND ssa.school_id IN (' . implode(",", $schoolIds) . ')';
        }

        $query .= ' ) a ';

        //$query .= ' ORDER BY a.ORDER_BY_1 DESC, a.ORDER_BY_2 ASC, a.ORDER_BY_3 DESC ';

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $userIds = array_map(function ($result) { return $result['id']; }, $results);

        if ($queryBuilder) {
            return $this->createQueryBuilder('u')->andWhere('u.id IN (:userIds)')->setParameter('userIds', $userIds);
        }

        return $this->findBy([
            'id' => $userIds,
        ]);
    }

    /**
     * @param $search
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllUsernames() {

        $query = 'select u.id, u.username from user u';

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $usernames = [];
        foreach($results as $result) {
            $usernames[$result['id']] = $result['username'];
        }

        return $usernames;
    }

    /**
     * @param $search
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllEmailAddresses() {

        $query = 'select u.id, u.email from user u';

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $emails = [];
        foreach($results as $result) {
            $emails[$result['id']] = strtolower($result['email']);
        }

        return $emails;
    }

}
