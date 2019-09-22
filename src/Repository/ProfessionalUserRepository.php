<?php

namespace App\Repository;

use App\Entity\ProfessionalUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProfessionalUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfessionalUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfessionalUser[]    findAll()
 * @method ProfessionalUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfessionalUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProfessionalUser::class);
    }

    // /**
    //  * @return ProfessionalUser[] Returns an array of ProfessionalUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfessionalUser
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
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

        $query = sprintf('SELECT u.id, u.first_name, u.last_name, "ROLE_PROFESSIONAL_USER" as role from user u inner join professional_user pu on u.id = pu.id where CONCAT(u.first_name, " ", u.last_name) LIKE "%%%s%%"', $search);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
