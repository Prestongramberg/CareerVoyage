<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Company::class);
    }

    // /**
    //  * @return Company[] Returns an array of Company objects
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
    public function findOneBySomeField($value): ?Company
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

        $whereClause = !empty($whereClause) ? sprintf('WHERE %s', implode(" OR ", $whereClause)) : '';

        $query = sprintf("select c.id, c.name, c.short_description from company c inner join company_secondary_industry csi on c.id = csi.company_id %s GROUP BY c.id order by c.created_at DESC LIMIT %s", $whereClause, $limit);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBySchool(School $school) {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.schools', 'schools')
            ->where('schools.id = :id')
            ->setParameter('id', $school->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * To use this function a few things must happen first
     * 1. You must use google api to find the starting latitude and starting longitude of the starting address or zipcode
     * 2. You must use the geocoder->calculateSearchSquare() service to return the 4 lat/lng points
     * 3. Then you can call this function!
     *
     * @param $latN
     * @param $latS
     * @param $lonE
     * @param $lonW
     * @param $startingLatitude
     * @param $startingLongitude
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByRadius($latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude) {

        $query = sprintf('SELECT id from company c WHERE c.latitude <= %s AND c.latitude >= %s AND c.longitude <= %s AND c.longitude >= %s AND (c.latitude != %s AND c.longitude != %s) AND c.approved = 1',
            $latN, $latS, $lonE, $lonW, $startingLatitude, $startingLongitude
        );

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $companyIds
     * @return mixed
     */
    public function getByArrayOfIds($companyIds) {

        return $this->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $companyIds)
            ->getQuery()
            ->getResult();
    }


    /**
     * @param $company_id
     * @return mixed
     */
    public function getActiveProfessionalUsers($company_id) {
        
        return $this->createQueryBuilder('c')
            ->innerJoin('c.professionalUsers', 'professionalUsers')
            ->where('c.id = :id')
            ->andWhere('professionalUsers.activated = :activated')
            ->andWhere('professionalUsers.deleted = :deleted')
            ->setParameter('id', $company_id)
            ->setParameter('activated', true)
            ->setParameter('deleted', false)
            ->getQuery()
            ->getResult();
    }

}
