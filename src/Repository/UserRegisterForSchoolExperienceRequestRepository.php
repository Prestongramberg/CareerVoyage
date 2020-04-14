<?php

namespace App\Repository;

use App\Entity\UserRegisterForSchoolExperienceRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Experience;
use App\Entity\SchoolExperience;
use App\Entity\User;

/**
 * @method UserRegisterForSchoolExperienceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRegisterForSchoolExperienceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRegisterForSchoolExperienceRequest[]    findAll()
 * @method UserRegisterForSchoolExperienceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRegisterForSchoolExperienceRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserRegisterForSchoolExperienceRequest::class);
    }

    // /**
    //  * @return UserRegisterForSchoolExperienceRequest[] Returns an array of UserRegisterForSchoolExperienceRequest objects
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
    public function findOneBySomeField($value): ?UserRegisterForSchoolExperienceRequest
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
     * @param User $user
     * @param SchoolExperience $experience
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByUserAndExperience(User $user, SchoolExperience $experience) {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user_id')
            ->andWhere('r.schoolExperience = :school_experience_id')
            ->setParameter('user_id', $user->getId())
            ->setParameter('school_experience_id', $experience->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
