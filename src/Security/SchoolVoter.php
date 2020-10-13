<?php

namespace App\Security;

use App\Entity\AdminUser;
use App\Entity\Company;
use App\Entity\Lesson;
use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SchoolVoter extends Voter
{
    // these strings are just invented: you can use anything
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        if (!$subject instanceof School) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var School $school
         */
        $school = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($school, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(School $school, User $user)
    {
        /** @var AdminUser $user */
        if($user->isAdmin()) {
            return true;
        }

        /** @var SiteAdminUser $user */
        if($user->isSiteAdmin()) {
            return $user->getSite() && $user->getSite()->getId() === $school->getSite()->getId();
        }

        /** @var StateCoordinator $user */
        if ( $user->isStateCoordinator() ) {
            return $user->getSite() && $user->getSite()->getId() === $school->getSite()->getId() && $user->getState()->getId() === $school->getState()->getId();
        }

        /** @var RegionalCoordinator $user */
        if ( $user->isRegionalCoordinator() ) {
            return $user->getSite() && $user->getSite()->getId() === $school->getSite()->getId() && $user->getRegion()->getId() === $school->getRegion()->getId();
        }

        /** @var EducatorUser $user */
        if ( $user->isEducator() ) {
            return $school->getAllowEventCreation();
        }

        return $school->isUserSchoolAdministrator($user);
    }
}