<?php

namespace App\Repository;

use App\Entity\CompanyExperience;
use App\Entity\SecondaryIndustry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompanyExperience|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyExperience|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyExperience[]    findAll()
 * @method CompanyExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompanyExperience::class);
    }

    // /**
    //  * @return CompanyExperience[] Returns an array of CompanyExperience objects
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
    public function findOneBySomeField($value): ?CompanyExperience
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
    inner join company_experience ce on ce.id = e.id 
    WHERE e.start_date_and_time >= CURDATE() and (%s) 
    GROUP BY ce.id order by e.start_date_and_time ASC LIMIT %s
HERE;

        $query = sprintf($query, $whereClause, $limit);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEventsGroupedByPrimaryIndustry() {

        $query = <<<HERE
    select count(ce.id) as num_of_company_events, i.name as primary_industry_name
    from company_experience ce 
    inner join experience e on e.id = ce.id
    inner join experience_secondary_industry esi on esi.experience_id = e.id
    inner join secondary_industry si on esi.secondary_industry_id = si.id
    inner join industry i on i.id = si.primary_industry_id
    where MONTH(e.start_date_and_time) = MONTH(CURRENT_DATE())
    AND YEAR(e.start_date_and_time) = YEAR(CURRENT_DATE())
    group by primary_industry_name
HERE;
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
