<?php

namespace App\Repository;

use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Feedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feedback[]    findAll()
 * @method Feedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    // /**
    //  * @return Feedback[] Returns an array of Feedback objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Feedback
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByEvent($event)
    {
        return $this->createQueryBuilder('f')
                    ->andWhere('f.experience = :event')
                    ->andWhere('f.deleted = :false')
                    ->setParameter('event', $event['id'])
                    ->setParameter('false', false)
                    ->orderBy('f.id', 'ASC')
                    ->getQuery()
                    ->getResult();
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getFilters()
    {
        // TODO COME BACK TO THIS
        $filters = [
            'feedback_provider' => [],
            'experience_provider' => [],
            'experience_type_name' => [],
            'region_name' => [],
            'school_name' => [],
            'company_name' => [],
        ];

        try {
            // Feedback Provider
            $query = 'select distinct feedback_provider from feedback where feedback_provider is not null order by feedback_provider ASC;';
            $em    = $this->getEntityManager();
            $stmt  = $em->getConnection()->prepare($query);
            $stmt->execute();
            $feedbackProviders            = $stmt->fetchAllNumeric();
            $filters['feedback_provider'] = array_reduce($feedbackProviders, function ($result, $item) {
                $result[$item[0]] = $item[0];

                return $result;
            }, []);
        } catch (\Exception $exception) {
            /* do nothing*/
        }

        try {
            // Experience Provider
            $query = 'select distinct experience_provider from feedback where experience_provider is not null order by experience_provider ASC;';
            $em    = $this->getEntityManager();
            $stmt  = $em->getConnection()->prepare($query);
            $stmt->execute();
            $experienceProviders            = $stmt->fetchAllNumeric();
            $filters['experience_provider'] = array_reduce($experienceProviders, function ($result, $item) {
                $result[$item[0]] = $item[0];

                return $result;
            }, []);
        } catch (\Exception $exception) {
            /* do nothing*/
        }

        try {
            // Experience Type Name
            $query = 'select distinct experience_type_name from feedback where experience_type_name is not null order by experience_type_name ASC;';
            $em    = $this->getEntityManager();
            $stmt  = $em->getConnection()->prepare($query);
            $stmt->execute();
            $experienceTypeNames             = $stmt->fetchAllNumeric();
            $filters['experience_type_name'] = array_reduce($experienceTypeNames, function ($result, $item) {
                $result[$item[0]] = $item[0];

                return $result;
            }, []);
        } catch (\Exception $exception) {
            /* do nothing*/
        }

        try {
            // Region Name
            $query = 'select distinct region_names from feedback where region_names is not null;';
            $em    = $this->getEntityManager();
            $stmt  = $em->getConnection()->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAllNumeric();

            $regionNames = [];
            foreach ($results as $regions) {

                if (empty($regions[0] || !is_string($regions[0]))) {
                    continue;
                }

                $regions = json_decode($regions[0], true);

                if (!is_array($regions) || empty($regions)) {
                    continue;
                }

                foreach ($regions as $region) {
                    $regionNames[] = $region;
                }
            }

            sort($regionNames);

            $filters['region_name'] = array_combine($regionNames, $regionNames);
        } catch (\Exception $exception) {
            /* do nothing*/
        }

        try {
            // School Name
            $query = 'select distinct school_names from feedback where school_names is not null;';
            $em    = $this->getEntityManager();
            $stmt  = $em->getConnection()->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAllNumeric();

            $schoolNames = [];
            foreach ($results as $schools) {

                if (empty($schools[0] || !is_string($schools[0]))) {
                    continue;
                }

                $schools = json_decode($schools[0], true);

                if (!is_array($schools) || empty($schools)) {
                    continue;
                }

                foreach ($schools as $school) {
                    $schoolNames[] = $school;
                }
            }

            sort($schoolNames);

            $filters['school_name'] = array_combine($schoolNames, $schoolNames);
        } catch (\Exception $exception) {
            /* do nothing*/
        }

        try {
            // Company Name
            $query = 'select distinct company_names from feedback where company_names is not null;';
            $em    = $this->getEntityManager();
            $stmt  = $em->getConnection()->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAllNumeric();

            $companyNames = [];
            foreach ($results as $companies) {

                if (empty($companies[0] || !is_string($companies[0]))) {
                    continue;
                }

                $companies = json_decode($companies[0], true);

                if (!is_array($companies) || empty($companies)) {
                    continue;
                }

                foreach ($companies as $company) {
                    $companyNames[] = $company;
                }
            }

            sort($companyNames);

            $filters['company_name'] = array_combine($companyNames, $companyNames);
        } catch (\Exception $exception) {
            /* do nothing*/
        }

        //select distinct region_names from feedback where region_names is not null;

        return $filters;
    }


    //select distinct feedback_provider from feedback where feedback_provider is not null;
}
