<?php

namespace App\Service;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\Report;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\User;
use App\Repository\ReportRepository;

class ReportService
{
    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * ReportService constructor.
     *
     * @param ReportRepository $reportRepository
     */
    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param User $user
     * @param bool $returnQueryBuilder
     *
     * @return \Doctrine\ORM\QueryBuilder|mixed
     */
    public function searchReports(User $user, $returnQueryBuilder = false)
    {

        if ($user->isProfessional()) {
            /** @var ProfessionalUser $user */

            $queryParts = [];

            $queryBuilder = $this->reportRepository->createQueryBuilder('report')
                                                   ->leftJoin('report.reportShare', 'reportShare')
                                                   ->leftJoin('reportShare.users', 'users')
                                                   ->leftJoin('reportShare.regions', 'regions')
                                                   ->leftJoin('reportShare.schools', 'schools')
                                                   ->andWhere('report.reportType = :reportType')
                                                   ->setParameter('reportType', Report::TYPE_BUILDER)
                                                   ->orderBy('report.id', 'DESC');

            $regions = array_map(function (Region $region) {
                return $region->getId();
            }, $user->getRegions()->toArray());

            $schools = array_map(function (School $school) {
                return $school->getId();
            }, $user->getSchools()->toArray());

            if (!empty($regions)) {
                $regionsImploded = implode("','", $regions);
                $queryParts[]    = sprintf("regions.id IN ('%s')", $regionsImploded);
            }

            if (!empty($schools)) {
                $schoolsImploded = implode("','", $schools);
                $queryParts[]    = sprintf("schools.id IN ('%s')", $schoolsImploded);
            }

            $queryParts[] = sprintf('users.id = %s', $user->getId());

            $queryParts[] = sprintf("reportShare.userRole = '%s'", User::ROLE_PROFESSIONAL_USER);

            if($user->isCompanyAdministrator()) {
                $queryParts[] = sprintf("reportShare.userRole = '%s'", User::ROLE_COMPANY_ADMINISTRATOR);
            }

            $queryString = implode(" OR ", $queryParts);

            $queryBuilder->andWhere($queryString);

        } elseif ($user->isEducator()) {
            /** @var EducatorUser $user */

            $queryParts = [];

            $queryBuilder = $this->reportRepository->createQueryBuilder('report')
                                                   ->leftJoin('report.reportShare', 'reportShare')
                                                   ->leftJoin('reportShare.users', 'users')
                                                   ->leftJoin('reportShare.regions', 'regions')
                                                   ->leftJoin('reportShare.schools', 'schools')
                                                   ->andWhere('report.reportType = :reportType')
                                                   ->setParameter('reportType', Report::TYPE_BUILDER)
                                                   ->orderBy('report.id', 'DESC');

            if ($school = $user->getSchool()) {

                $queryParts[] = sprintf("schools.id = %s", $school->getId());

                if ($region = $school->getRegion()) {
                    $queryParts[] = sprintf("regions.id = %s", $region->getId());
                }
            }

            $queryParts[] = sprintf('users.id = %s', $user->getId());

            $queryParts[] = sprintf("reportShare.userRole = '%s'", User::ROLE_EDUCATOR_USER);

            $queryString = implode(" OR ", $queryParts);

            $queryBuilder->andWhere($queryString);

        } elseif ($user->isSchoolAdministrator()) {
            /** @var SchoolAdministrator $user */

            $queryParts = [];
            $regions    = [];
            $schools    = [];

            $queryBuilder = $this->reportRepository->createQueryBuilder('report')
                                                   ->leftJoin('report.reportShare', 'reportShare')
                                                   ->leftJoin('reportShare.users', 'users')
                                                   ->leftJoin('reportShare.regions', 'regions')
                                                   ->leftJoin('reportShare.schools', 'schools')
                                                   ->andWhere('report.reportType = :reportType')
                                                   ->setParameter('reportType', Report::TYPE_BUILDER)
                                                   ->orderBy('report.id', 'DESC');

            foreach ($user->getSchools() as $school) {

                $schools[] = $school->getId();

                if ($region = $school->getRegion()) {
                    $regions[] = $region->getId();
                }
            }

            if (!empty($regions)) {
                $regionsImploded = implode("','", $regions);
                $queryParts[]    = sprintf("regions.id IN ('%s')", $regionsImploded);
            }

            if (!empty($schools)) {
                $schoolsImploded = implode("','", $schools);
                $queryParts[]    = sprintf("schools.id IN ('%s')", $schoolsImploded);
            }

            $queryParts[] = sprintf('users.id = %s', $user->getId());

            $queryParts[] = sprintf("reportShare.userRole = '%s'", User::ROLE_PROFESSIONAL_USER);

            $queryString = implode(" OR ", $queryParts);

            $queryBuilder->andWhere($queryString);

        } elseif ($user->isRegionalCoordinator()) {
            /** @var RegionalCoordinator $user */

            $queryParts = [];

            $queryBuilder = $this->reportRepository->createQueryBuilder('report')
                                                   ->leftJoin('report.reportShare', 'reportShare')
                                                   ->leftJoin('reportShare.users', 'users')
                                                   ->leftJoin('reportShare.regions', 'regions')
                                                   ->leftJoin('reportShare.schools', 'schools')
                                                   ->andWhere('report.reportType = :reportType')
                                                   ->setParameter('reportType', Report::TYPE_BUILDER)
                                                   ->orderBy('report.id', 'DESC');

            if ($region = $user->getRegion()) {
                $queryParts[] = sprintf("regions.id = %s", $region->getId());

                $schools = [];
                foreach ($region->getSchools() as $school) {
                    $schools[] = $school->getId();
                }

                if (!empty($schools)) {
                    $schoolsImploded = implode("','", $schools);
                    $queryParts[]    = sprintf("schools.id IN ('%s')", $schoolsImploded);
                }
            }

            $queryParts[] = sprintf('users.id = %s', $user->getId());

            $queryParts[] = sprintf("reportShare.userRole = '%s'", User::ROLE_PROFESSIONAL_USER);

            $queryString = implode(" OR ", $queryParts);

            $queryBuilder->andWhere($queryString);

        } else {
            /** @var User $user */

            $queryParts = [];

            $queryBuilder = $this->reportRepository->createQueryBuilder('report')
                                                   ->leftJoin('report.reportShare', 'reportShare')
                                                   ->leftJoin('reportShare.users', 'users')
                                                   ->andWhere('report.reportType = :reportType')
                                                   ->setParameter('reportType', Report::TYPE_BUILDER)
                                                   ->orderBy('report.id', 'DESC');

            $queryParts[] = sprintf('users.id = %s', $user->getId());

            $queryString = implode(" OR ", $queryParts);

            $queryBuilder->andWhere($queryString);
        }

        if ($returnQueryBuilder) {
            return $queryBuilder;
        }

        return $queryBuilder->getQuery()->getResult();
    }

}