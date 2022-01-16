<?php

namespace App\Util;

use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\User;

class AuthorizationVoter
{

    /*************************START EXPERIENCE********************************/

    public function canCreateExperiencesForSchool(User $user, School $school = null)
    {

        if ($user->isAdmin()) {
            return true;
        }

        if(!$school) {
            return false;
        }

        if(!$user->isSchoolAdministrator() && !$user->isEducator()) {
            return false;
        }

        if($user instanceof SchoolAdministrator) {
            foreach ($user->getSchools() as $schoolObject) {
                if ($school->getId() === $schoolObject->getId()) {
                    return true;
                }
            }
        }

        if($user instanceof EducatorUser) {
            if($school->getAllowEventCreation() && $user->getSchool() && $user->getSchool()->getId() === $school->getId()) {
                return true;
            }
        }

        return false;
    }

    public function canEditExperience(User $user, Experience $experience)
    {

        if($experience->getCreator() && $experience->getCreator()->getId() === $user->getId()) {
            return true;
        }

        if ($experience instanceof SchoolExperience) {
            /** @var SchoolExperience $experience */

            if (!$experience->getSchool()) {
                return false;
            }

            if ($user->isSchoolAdministrator()) {
                /** @var SchoolAdministrator $user */
                foreach ($user->getSchools() as $school) {
                    if ($school->getId() === $experience->getSchool()->getId()) {
                        return true;
                    }
                }
            }

            if ($user->isEducator()) {
                /** @var EducatorUser $user */

                if (!$user->getSchool()) {
                    return false;
                }

                if ($user->getSchool()->getId() !== $experience->getSchool()->getId()) {
                    return false;
                }

                if ($experience->getSchoolContact() && $experience->getSchoolContact()->getId() === $user->getId()) {
                    return true;
                }
            }
        }

        if ($experience instanceof CompanyExperience) {

            if ($experience->getEmployeeContact() && $experience->getEmployeeContact()->getId() === $user->getId()) {
                return true;
            }

            if ($experience->getCompany() && $experience->getCompany()->getOwner() && $experience->getCompany()
                                                                                                 ->getOwner()
                                                                                                 ->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function canDeleteExperience(User $user, Experience $experience)
    {

        // experiences cannot be deleted that aren't cancelled
        if (!$experience->getCancelled()) {
            return false;
        }

        return $this->canEditExperience($user, $experience);
    }

    public function canCancelExperience(User $user, Experience $experience)
    {

        // experiences cannot be cancelled that are already cancelled
        if ($experience->getCancelled()) {
            return false;
        }

        return $this->canEditExperience($user, $experience);
    }

    public function canAddExperienceToCalendar(User $user, Experience $experience)
    {

        if($experience->getIsRecurring()) {
            return false;
        }

        if ($experience->getEndDateAndTime() < new \DateTime()) {
            return false;
        }

        if ($experience->getCancelled()) {
            return false;
        }

        foreach ($experience->getRegistrations() as $registration) {

            if (!$registration->getUser()) {
                continue;
            }

            if ($registration->getUser()->getId() === $user->getId()) {
                return true;
            }

        }

        return false;
    }

    public function canRegisterForExperience(User $user, Experience $experience)
    {
        if($experience->getIsRecurring()) {
            return false;
        }

        if ($experience->getCancelled()) {
            return false;
        }

        if ($experience->getEndDateAndTime() < new \DateTime()) {
            return false;
        }

        foreach ($experience->getRegistrations() as $registration) {

            if (!$registration->getUser()) {
                continue;
            }

            if ($registration->getUser()->getId() === $user->getId()) {
                return false;
            }

        }

        return true;
    }

    public function canUnregisterForExperience(User $user, Experience $experience)
    {
        if($experience->getIsRecurring()) {
            return false;
        }

        if ($experience->getCancelled()) {
            return false;
        }

        if ($experience->getEndDateAndTime() < new \DateTime()) {
            return false;
        }

        foreach ($experience->getRegistrations() as $registration) {

            if (!$registration->getUser()) {
                continue;
            }

            if ($registration->getUser()->getId() === $user->getId()) {
                return true;
            }

        }

        return false;
    }

    public function canRegisterStudentsForExperience(User $user, Experience $experience)
    {
        if($experience->getIsRecurring()) {
            return false;
        }

        if ($experience->getCancelled()) {
            return false;
        }

        if ($experience->getEndDateAndTime() < new \DateTime()) {
            return false;
        }

        if($user->isSchoolAdministrator()) {
            return true;
        }

        if($user->isEducator()) {
            return true;
        }

        return false;
    }



    /************************* END EXPERIENCE********************************/


    /************************* START SCHOOL ********************************/

    public function canEditSchool(User $user, School $school)
    {

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSchoolAdministrator()) {
            /** @var SchoolAdministrator $user */
            foreach ($user->getSchools() as $schoolObject) {
                if ($school->getId() === $schoolObject->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canManageStudents(User $user) {

        if ($user->isAdmin()) {
            return true;
        }

        if($user->isSchoolAdministrator()) {
            return true;
        }

        if($user instanceof EducatorUser && $user->getSchool()) {
            return true;
        }

        return false;
    }

    public function canImportStudents(User $user) {

        if($user->isSchoolAdministrator()) {
            return true;
        }

        return false;
    }

    public function canManageEducators(User $user) {

        if ($user->isAdmin()) {
            return true;
        }

        if($user->isSchoolAdministrator()) {
            return true;
        }

        return false;
    }


    /************************* END SCHOOL ********************************/


    /************************* START COMPANY ********************************/

    public function canEditCompany(User $user, Company $company)
    {

        if ($user->isAdmin()) {
            return true;
        }

        if ($company->getOwner() && $company->getOwner()->getId() === $user->getId()) {
            return true;
        }

        return false;
    }


    /************************* END COMPANY ********************************/
}