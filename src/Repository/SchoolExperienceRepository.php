<?php

namespace App\Repository;

use App\Entity\SchoolExperience;
use App\Entity\SecondaryIndustry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolExperience[]    findAll()
 * @method SchoolExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolExperience::class);
    }

    // /**
    //  * @return SchoolExperience[] Returns an array of SchoolExperience objects
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
    public function findOneBySomeField($value): ?SchoolExperience
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
     * @param SecondaryIndustry $secondaryIndustries []
     * @param int $limit
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBySecondaryIndustries($secondaryIndustries, $limit = 6) {

        $whereClause = [];
        foreach($secondaryIndustries as $secondaryIndustry) {
            $whereClause[] = sprintf("secondary_industry_id = %s", $secondaryIndustry->getId());
        }

        $whereClause = !empty($whereClause) ? implode(" OR ", $whereClause) : '';

        $query = <<<HERE
    select e.id, e.title, e.brief_description from experience e 
    inner join experience_secondary_industry esi on e.id = esi.experience_id 
    inner join school_experience se on se.id = e.id 
    WHERE e.start_date_and_time >= CURDATE() and (%s) 
    GROUP BY se.id order by e.start_date_and_time ASC LIMIT %s
HERE;

        $query = sprintf($query, $whereClause, $limit);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
