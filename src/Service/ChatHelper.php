<?php

namespace App\Service;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Repository\AdminUserRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\SchoolAdministratorRepository;
use App\Repository\SiteAdminUserRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StudentUserRepository;
use App\Repository\SystemUserRepository;
use App\Repository\UserRepository;

class ChatHelper
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
     * @var SystemUserRepository
     */
    private $systemUserRepository;

    /**
     * @var AdminUserRepository
     */
    private $adminUserRepository;

    /**
     * ChatHelper constructor.
     *
     * @param ProfessionalUserRepository    $professionalUserRepository
     * @param UserRepository                $userRepository
     * @param StudentUserRepository         $studentUserRepository
     * @param EducatorUserRepository        $educatorUserRepository
     * @param SiteAdminUserRepository       $siteAdminRepository
     * @param SchoolAdministratorRepository $schoolAdministratorRepository
     * @param StateCoordinatorRepository    $stateCoordinatorRepository
     * @param SystemUserRepository          $systemUserRepository
     * @param AdminUserRepository           $adminUserRepository
     */
    public function __construct(
        ProfessionalUserRepository $professionalUserRepository, UserRepository $userRepository,
        StudentUserRepository $studentUserRepository, EducatorUserRepository $educatorUserRepository,
        SiteAdminUserRepository $siteAdminRepository, SchoolAdministratorRepository $schoolAdministratorRepository,
        StateCoordinatorRepository $stateCoordinatorRepository, SystemUserRepository $systemUserRepository,
        AdminUserRepository $adminUserRepository
    ) {
        $this->professionalUserRepository    = $professionalUserRepository;
        $this->userRepository                = $userRepository;
        $this->studentUserRepository         = $studentUserRepository;
        $this->educatorUserRepository        = $educatorUserRepository;
        $this->siteAdminRepository           = $siteAdminRepository;
        $this->schoolAdministratorRepository = $schoolAdministratorRepository;
        $this->stateCoordinatorRepository    = $stateCoordinatorRepository;
        $this->systemUserRepository          = $systemUserRepository;
        $this->adminUserRepository           = $adminUserRepository;
    }

    /**
     * @param User   $loggedInUser
     * @param string $search
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getChattableUsers(User $loggedInUser, $search = '')
    {

        $users = [];

        /**
         * Students can message
         * 1. Educators that are part of the same school
         * 2. School administrators that are part of the same school
         * 3. Students that are part of the same school
         *
         * @var StudentUser $loggedInUser
         */
        if ($loggedInUser->isStudent()) {
            $adminUsers           = $this->adminUserRepository->findBySearchTerm($search);
            $educatorUsers        = $this->educatorUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            // for now we are disabling student to student communication
            //$studentUsers = $this->studentUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $professionalUsers = $this->professionalUserRepository->findByAllowedCommunication($search, $loggedInUser);
            $users             = array_merge($professionalUsers, $educatorUsers, $schoolAdministrators, $adminUsers);
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
            $adminUsers           = $this->adminUserRepository->findBySearchTerm($search);
            $educatorUsers        = $this->educatorUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $studentUsers         = $this->studentUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $professionalUsers    = $this->professionalUserRepository->findBySearchTerm($search);
            $users                = array_merge($educatorUsers, $schoolAdministrators, $studentUsers, $professionalUsers, $adminUsers);
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
            $adminUsers           = $this->adminUserRepository->findBySearchTerm($search);
            $educatorUsers        = $this->educatorUserRepository->findBySearchTerm($search);
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTerm($search);
            $professionalUsers    = $this->professionalUserRepository->findBySearchTerm($search);
            $studentUsers         = $this->studentUserRepository->findByAllowedCommunication($search, $loggedInUser);
            $users                = array_merge($studentUsers, $educatorUsers, $schoolAdministrators, $professionalUsers, $adminUsers);
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

            $adminUsers = $this->adminUserRepository->findBySearchTerm($search);

            // find users just for their school
            foreach ($loggedInUser->getSchools() as $school) {
                $educatorUsers        = $this->educatorUserRepository->findBySearchTermAndSchool($search, $school);
                $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTerm($search);
                $studentUsers         = $this->studentUserRepository->findBySearchTermAndSchool($search, $school);
                $users                = array_merge($users, $studentUsers, $educatorUsers, $schoolAdministrators);
            }

            $professionalUsers = $this->professionalUserRepository->findBySearchTerm($search);

            $users = array_merge($users, $professionalUsers, $adminUsers);
        }

        $systemUsers = $this->systemUserRepository->findBySearchTerm($search);

        $users = array_merge($users, $systemUsers);

        return $users;
    }

}