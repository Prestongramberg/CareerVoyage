<?php

namespace App\Service;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Model\GlobalShareFilters;
use App\Repository\EducatorUserRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\SchoolAdministratorRepository;
use App\Repository\SiteAdminUserRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StudentUserRepository;
use App\Repository\UserRepository;

class GlobalShare
{
    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * @var EducatorUserRepository
     */
    private $educatorUserRepository;

    /**
     * @var SiteAdminUserRepository
     */
    private $siteAdminRepository;

    /**
     * @var SchoolAdministratorRepository
     */
    private $schoolAdministratorRepository;

    /**
     * @var StateCoordinatorRepository
     */
    private $stateCoordinatorRepository;

    /**
     * GlobalShare constructor.
     *
     * @param ProfessionalUserRepository    $professionalUserRepository
     * @param UserRepository                $userRepository
     * @param StudentUserRepository         $studentUserRepository
     * @param EducatorUserRepository        $educatorUserRepository
     * @param SiteAdminUserRepository       $siteAdminRepository
     * @param SchoolAdministratorRepository $schoolAdministratorRepository
     * @param StateCoordinatorRepository    $stateCoordinatorRepository
     */
    public function __construct(
        ProfessionalUserRepository $professionalUserRepository, UserRepository $userRepository,
        StudentUserRepository $studentUserRepository, EducatorUserRepository $educatorUserRepository,
        SiteAdminUserRepository $siteAdminRepository, SchoolAdministratorRepository $schoolAdministratorRepository,
        StateCoordinatorRepository $stateCoordinatorRepository
    ) {
        $this->professionalUserRepository    = $professionalUserRepository;
        $this->userRepository                = $userRepository;
        $this->studentUserRepository         = $studentUserRepository;
        $this->educatorUserRepository        = $educatorUserRepository;
        $this->siteAdminRepository           = $siteAdminRepository;
        $this->schoolAdministratorRepository = $schoolAdministratorRepository;
        $this->stateCoordinatorRepository    = $stateCoordinatorRepository;
    }

    /**
     * @param User               $loggedInUser
     * @param GlobalShareFilters $filters
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getData(User $loggedInUser, GlobalShareFilters $filters = null)
    {

        $payload = [
            'professionals' => [],
            'educators'     => [],
            'school_admins' => [],
            'students'      => [],
            'all'           => [],
            'filters'       => [],
        ];

        $chattableUsers = $this->getShareableUsers($loggedInUser, $filters);

        $chattableUserIds = array_map(
            function ($chattableUser) {
                return $chattableUser['id'];
            }, $chattableUsers
        );

        $professionals = $this->professionalUserRepository->getDataForGlobalShare($chattableUserIds, $filters);

        foreach ($professionals as $professional) {

            $payload['professionals'][$professional['id']]['company_administrator'] = $professional['company_administrator'];
            $payload['professionals'][$professional['id']]['company_id']            = $professional['company_id'];
            $payload['professionals'][$professional['id']]['company_name']          = $professional['company_name'];
            $payload['professionals'][$professional['id']]['email']                 = $professional['email'];
            $payload['professionals'][$professional['id']]['first_name']            = $professional['first_name'];
            $payload['professionals'][$professional['id']]['id']                    = $professional['id'];
            $payload['professionals'][$professional['id']]['last_name']             = $professional['last_name'];
            $payload['professionals'][$professional['id']]['interests']             = $professional['interests'];
            $payload['professionals'][$professional['id']]['user_role']             = $professional['user_role'];
            $payload['professionals'][$professional['id']]['photoImageURL']         = $professional['photoImageURL'];

            $payload['professionals'][$professional['id']]['roles'][$professional['role_id']] = [
                'role_id'   => $professional['role_id'],
                'role_name' => $professional['role_name'],
            ];

            $payload['professionals'][$professional['id']]['secondary_industries'][$professional['secondary_industry_id']] = [
                'secondary_industry_id'   => $professional['secondary_industry_id'],
                'secondary_industry_name' => $professional['secondary_industry_name'],
                'primary_industry_id'     => $professional['primary_industry_id'],
                'primary_industry_name'   => $professional['primary_industry_name'],
            ];
        }


        $educators = $this->educatorUserRepository->getDataForGlobalShare($chattableUserIds, $filters);

        foreach ($educators as $educator) {

            $payload['educators'][$educator['id']]['school_name']   = $educator['school_name'];
            $payload['educators'][$educator['id']]['school_id']     = $educator['school_id'];
            $payload['educators'][$educator['id']]['email']         = $educator['email'];
            $payload['educators'][$educator['id']]['first_name']    = $educator['first_name'];
            $payload['educators'][$educator['id']]['id']            = $educator['id'];
            $payload['educators'][$educator['id']]['last_name']     = $educator['last_name'];
            $payload['educators'][$educator['id']]['interests']     = $educator['interests'];
            $payload['educators'][$educator['id']]['user_role']     = $educator['user_role'];
            $payload['educators'][$educator['id']]['photoImageURL'] = $educator['photoImageURL'];

            $payload['educators'][$educator['id']]['courses'][$educator['course_id']] = [
                'course_id'    => $educator['course_id'],
                'course_title' => $educator['course_title'],
            ];

            $payload['educators'][$educator['id']]['secondary_industries'][$educator['secondary_industry_id']] = [
                'secondary_industry_id'   => $educator['secondary_industry_id'],
                'secondary_industry_name' => $educator['secondary_industry_name'],
                'primary_industry_id'     => $educator['primary_industry_id'],
                'primary_industry_name'   => $educator['primary_industry_name'],
            ];
        }

        $schoolAdmins = $this->schoolAdministratorRepository->getDataForGlobalShare($chattableUserIds, $filters);

        foreach ($schoolAdmins as $schoolAdmin) {
            $payload['school_admins'][$schoolAdmin['id']]['email']         = $schoolAdmin['email'];
            $payload['school_admins'][$schoolAdmin['id']]['first_name']    = $schoolAdmin['first_name'];
            $payload['school_admins'][$schoolAdmin['id']]['id']            = $schoolAdmin['id'];
            $payload['school_admins'][$schoolAdmin['id']]['last_name']     = $schoolAdmin['last_name'];
            $payload['school_admins'][$schoolAdmin['id']]['user_role']     = $schoolAdmin['user_role'];
            $payload['school_admins'][$schoolAdmin['id']]['photoImageURL'] = $schoolAdmin['photoImageURL'];

            $payload['school_admins'][$schoolAdmin['id']]['schools'][$schoolAdmin['school_id']] = [
                'school_id'   => $schoolAdmin['school_id'],
                'school_name' => $schoolAdmin['school_name'],
            ];
        }

        $students = $this->studentUserRepository->getDataForGlobalShare($chattableUserIds, $filters);

        foreach ($students as $student) {

            $payload['students'][$student['id']]['school_name']   = $student['school_name'];
            $payload['students'][$student['id']]['school_id']     = $student['school_id'];
            $payload['students'][$student['id']]['email']         = $student['email'];
            $payload['students'][$student['id']]['username']      = $student['username'];
            $payload['students'][$student['id']]['first_name']    = $student['first_name'];
            $payload['students'][$student['id']]['id']            = $student['id'];
            $payload['students'][$student['id']]['last_name']     = $student['last_name'];
            $payload['students'][$student['id']]['interests']     = $student['interests'];
            $payload['students'][$student['id']]['user_role']     = $student['user_role'];
            $payload['students'][$student['id']]['photoImageURL'] = $student['photoImageURL'];

            $payload['students'][$student['id']]['secondary_industries'][$student['secondary_industry_id']] = [
                'secondary_industry_id'   => $student['secondary_industry_id'],
                'secondary_industry_name' => $student['secondary_industry_name'],
                'primary_industry_id'     => $student['primary_industry_id'],
                'primary_industry_name'   => $student['primary_industry_name'],
            ];
        }

        $payload['all'] = array_merge([], $payload['professionals'], $payload['students'], $payload['school_admins'], $payload['educators']);

        $payload['total_count']  = count($payload['professionals']) + count($payload['students']) + count($payload['school_admins']) + count($payload['educators']);
        $payload['current_page'] = $filters ? $filters->getPage() : 1;
        $payload['total_pages']  = ceil(count($chattableUsers) / GlobalShareFilters::ITEMS_PER_PAGE);

        $payload['all'] = $filters ? array_slice($payload['all'], $filters->getOffset(), GlobalShareFilters::ITEMS_PER_PAGE) : $payload['all'];

        return $payload;
    }

    /**
     * @param User               $loggedInUser
     * @param GlobalShareFilters $filters
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getShareableUsers(User $loggedInUser, GlobalShareFilters $filters = null)
    {

        $users                = [];
        $educatorUsers        = [];
        $schoolAdministrators = [];
        $studentUsers         = [];
        $professionalUsers    = [];
        $regionIds            = [];

        /**
         * Students can message
         * 1. Educators that are part of the same school
         * 2. School administrators that are part of the same school
         * 3. Students that are part of the same school
         *
         * @var StudentUser $loggedInUser
         */
        if ($loggedInUser->isStudent()) {

            /** @var StudentUser $loggedInUser */
            $regionIds = [];

            if ($loggedInUser->getSchool() && $loggedInUser->getSchool()->getRegion()) {
                $regionIds[] = $loggedInUser->getSchool()->getRegion()->getId();
            }
        }

        /**
         * Educators can message
         * 1. Educators that are part of the same school
         * 2. School administrators that are part of the same school
         * 3. Students that are part of the same school
         * 4. All Professional Users
         *
         * @var EducatorUser $loggedInUser
         */
        if ($loggedInUser->isEducator()) {

            /** @var EducatorUser $loggedInUser */
            $regionIds = [];

            if ($loggedInUser->getSchool() && $loggedInUser->getSchool()->getRegion()) {
                $regionIds[] = $loggedInUser->getSchool()->getRegion()->getId();
            }
        }

        /**
         * Professionals can message
         * 1. All educators on the platform
         * 2. All school administrators
         * 4. All Professional Users
         *
         * @var ProfessionalUser $loggedInUser
         */
        if ($loggedInUser->isProfessional()) {

            /** @var ProfessionalUser $loggedInUser */
            $regionIds = [];
            foreach ($loggedInUser->getRegions() as $region) {
                $regionIds[] = $region->getId();
            }
        }

        /**
         * School Administrators can message
         * 1. All educators on the platform
         * 2. All school administrators
         * 4. All Professional Users
         *
         * @var SchoolAdministrator $loggedInUser
         */
        if ($loggedInUser->isSchoolAdministrator()) {

            $regionIds = [];
            /** @var SchoolAdministrator $loggedInUser */
            foreach ($loggedInUser->getSchools() as $school) {

                if (!$school->getRegion()) {
                    continue;
                }

                $regionIds[] = $school->getRegion()->getId();
            }
        }

        if ($filters === null) {
            $professionalUsers    = $this->professionalUserRepository->findBySearchTermAndRegionIds('', $regionIds);
            $educatorUsers        = $this->educatorUserRepository->findBySearchTermAndRegionIds('', $regionIds);
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTermAndRegionIds('', $regionIds);
            $studentUsers         = $this->studentUserRepository->findBySearchTermAndRegionIds('', $regionIds);

            $users = array_merge($users, $educatorUsers, $schoolAdministrators, $studentUsers, $professionalUsers);

            return $users;
        }


        if (!$filters->hasFilterByStudent() &&
            !$filters->hasFilterBySchoolAdministrator() &&
            !$filters->hasFilterByEducator() &&
            !$filters->hasFilterByProfessional() &&
            !$filters->hasFilterByCompanyAdministrator()) {

        $professionalUsers    = $this->professionalUserRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);
        $educatorUsers        = $this->educatorUserRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);
        $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);
        $studentUsers         = $this->studentUserRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);

        $users = array_merge($users, $educatorUsers, $schoolAdministrators, $studentUsers, $professionalUsers);

        return $users;
    }

        if ($filters->hasFilterByProfessional() || $filters->hasFilterByCompanyAdministrator()) {
            $professionalUsers = $this->professionalUserRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);
        }

        if ($filters->hasFilterByEducator()) {
            $educatorUsers = $this->educatorUserRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);
        }

        if ($filters->hasFilterBySchoolAdministrator()) {
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);
        }

        if ($filters->hasFilterByStudent()) {

            /** @var EducatorUser $loggedInUser */
            if ($loggedInUser->isEducator()) {
                $studentUsers = $this->studentUserRepository->findBySearchTermAndSchool($filters->getSearch(), $loggedInUser->getSchool());
            } else {
                $studentUsers = $this->studentUserRepository->findBySearchTermAndRegionIds($filters->getSearch(), $regionIds);
            }
        }

        $users = array_merge($users, $educatorUsers, $schoolAdministrators, $studentUsers, $professionalUsers);

        return $users;
    }


}