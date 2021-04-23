<?php

namespace App\Repository;

use App\Entity\CompanyView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @method CompanyView|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyView|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyView[]    findAll()
 * @method CompanyView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyView::class);
    }

    // /**
    //  * @return CompanyView[] Returns an array of CompanyView objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompanyView
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param int $school
     * @param int $user
     * @return mixed
     */
    public function getLastCompanyView($company_id, $user_id) {
        return $this->createQueryBuilder('c')
            ->andWhere('c.company = :company_id')
            ->andWhere('c.user = :user_id')
            ->orderBy('c.created_at', "DESC")
            ->setMaxResults(1)
            ->setParameter('company_id', $company_id)
            ->setParameter('user_id', $user_id)
            ->getQuery()
            ->getResult();
    }


    /**
     * @param int $days
     * @param int $company
     * @return mixed
     */
    public function getVisits($days, $company) {
        $dt = new DateTime();
        $day = $dt->modify("-".$days." day");
        $now = date('Y-m-d H:i:s');

        return $this->createQueryBuilder('c')
            ->andWhere('c.company = :company_id')
            ->andWhere('c.created_at BETWEEN :start AND :now')
            ->setParameter('company_id', $company)
            ->setParameter('start', $day->format('Y-m-d H:i:s'))
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }
}
