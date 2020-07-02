<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\StudentUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

use App\Entity\EducatorUser;



use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements  UserLoaderInterface
{
    public function __construct(RegistryInterface $registry)
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
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getByEmailAddress($emailAddress) {
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
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getByInvitationCode($invitationCode) {

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
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getByActivationCode($activationCode) {

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
     * @return User|null
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function getByPasswordResetToken($token) {
        return $this->createQueryBuilder('u')
            ->where('u.passwordResetToken = :token')
            ->setParameter('token', $token)
            ->andWhere('u.passwordResetTokenTimestamp >= :timestamp')
            ->setParameter('timestamp', new \DateTime('-23 hours 59 minutes 59 seconds'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $token
     * @return User|null
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function getByTemporarySecurityToken($token) {

        return $this->createQueryBuilder('u')
            ->where('u.temporarySecurityToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findByRole($role)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }


    public function findContactsBySchool($school)
    {
      $query = sprintf('SELECT u.id, u.first_name, u.last_name
                        FROM user u, school_school_administrator ssa, educator_user eu
                        WHERE ( (u.id = ssa.school_administrator_id AND ssa.school_id = ?) OR (u.id = eu.id AND eu.school_id = ?) ) AND eu.id != ssa.school_administrator_id
                        GROUP BY u.id ORDER BY last_name, first_name');

      return $this->createQueryBuilder('user')
          ->leftJoin('App\Entity\EducatorUser', 'eu', 'WITH', 'user.id = eu.id')
          ->where('eu.school = :school')
          ->setParameter('school', $school)
          ->orderBy("user.lastName");



      // $em = $this->getEntityManager();
      // $rsm = new ResultSetMapping();
      // $rsm->addEntityResult('App\Entity\User', 'u');
      // $rsm->addFieldResult('u', 'id', 'id');
      // $rsm->addFieldResult('u', 'firstName', 'first_name');
      // $rsm->addFieldResult('u', 'lastName', 'last_name');
      //
      // $query = $em->createNativeQuery("SELECT u.id, u.first_name, u.last_name FROM user u, educator_user eu WHERE u.id = eu.id AND eu.school_id = ?", $rsm);
      // $query->setParameter(1, 6);
      //
      //
      // $users = $query->getResult();
      //
      // print_r($users);
    }



    /**
     * @param Lesson $lesson
     * @return mixed
     */
    public function getUsersWhoCanTeachLesson(Lesson $lesson) {
        return $this->createQueryBuilder('u')
            ->join('u.lessonTeachables', 'lt')
            ->join('lt.lesson', 'lesson')
            ->where('lesson.id = :id')
            ->setParameter('id', $lesson->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $search
     * @param User $user
     * @return mixed
     */
    public function searchChatUsers($search, User $user) {

        $query = $this->createQueryBuilder('u');

        /** @var StudentUser $user */
        if($user->isStudent()) {
            $query->where('u.roles LIKE :roles')
                ->andWhere('u.')
                ->setParameter('roles', '%"'.User::ROLE_EDUCATOR_USER.'"%');
        }


           /* ->where('u.firstName LIKE :searchTerm OR u.lastName LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$search.'%')
            ->getQuery()
            ->getResult();*/
    }
}
