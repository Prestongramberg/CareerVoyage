<?php

namespace App\Repository;

use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Proxies\__CG__\App\Entity\StudentUser;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Registration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Registration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registration[]    findAll()
 * @method Registration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    // /**
    //  * @return Registration[] Returns an array of Registration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Registration
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param User $user
     * @param Experience $experience
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByUserAndExperience(User $user, Experience $experience) {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.experience = :experience')
            ->setParameter('user', $user)
            ->setParameter('experience', $experience)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param EducatorUser $educatorUser
     * @param Experience $experience
     * @return mixed
     */
    public function getAllByEducatorAndExperience(EducatorUser $educatorUser, Experience $experience) {

        $studentIds = [];
        foreach($educatorUser->getStudentUsers() as $studentUser) {
            $studentIds[] = $studentUser->getId();
        }

        return $this->createQueryBuilder('r')
            ->where('r.user IN (:ids)')
            ->andWhere('r.experience = :experience')
            ->setParameter('ids', $studentIds)
            ->setParameter('experience', $experience)
            ->getQuery()
            ->getResult();
    }
}
