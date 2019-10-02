<?php

namespace App\Repository;

use App\Entity\Experience;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Experience|null find($id, $lockMode = null, $lockVersion = null)
 * @method Experience|null findOneBy(array $criteria, array $orderBy = null)
 * @method Experience[]    findAll()
 * @method Experience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Experience::class);
    }

    // /**
    //  * @return Experience[] Returns an array of Experience objects
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
    public function findOneBySomeField($value): ?Experience
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getUpcomingEventsRegisteredForByUser(User $user) {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.registrations', 'r')
            ->where('r.user = :user')
            ->andWhere('e.startDateAndTime >= :startDateAndTime')
            ->setParameter('user', $user)
            ->setParameter('startDateAndTime' , new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function getAllEventsRegisteredForByUser(User $user) {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.registrations', 'r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getCompletedEventsRegisteredForByUser(User $user) {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.registrations', 'r')
            ->where('r.user = :user')
            ->andWhere('e.startDateAndTime <= :startDateAndTime')
            ->setParameter('user', $user)
            ->setParameter('startDateAndTime' , new \DateTime())
            ->getQuery()
            ->getResult();
    }
}
