<?php

namespace App\Repository;

use App\Entity\SystemUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SystemUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemUser[]    findAll()
 * @method SystemUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemUser::class);
    }

    // /**
    //  * @return SystemUser[] Returns an array of SystemUser objects
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
    public function findOneBySomeField($value): ?SystemUser
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
     * @param $search
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySearchTerm($search) {

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "SYSTEM_USER" as role, CONCAT("/media/cache/squared_thumbnail_small/uploads/profile_photo/", u.photo) as photoImageURL from user u inner join system_user su on u.id = su.id where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"', $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
