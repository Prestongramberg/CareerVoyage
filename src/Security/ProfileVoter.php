<?php

namespace App\Security;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProfileVoter extends Voter
{
    // these strings are just invented: you can use anything
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof User) {
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

        /** @var User $userToVoteOn */
        $userToVoteOn = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($userToVoteOn, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    public static function canEdit(User $userToVoteOn, User $user)
    {
        // admins can edit everyone!
        if($user->isAdmin()) {
            return true;
        }

        // you can always edit your own user account. duhhhhhh
        if($user->getId() === $userToVoteOn->getId()) {
            return true;
        }

        /** @var StateCoordinator $userToVoteOn */
        if ( $userToVoteOn->isStateCoordinator() ) {
            return $user->isSiteAdmin() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId();
        }

        /** @var RegionalCoordinator $userToVoteOn */
        if ( $userToVoteOn->isRegionalCoordinator() ) {
            return (
                ( $user->isSiteAdmin() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() ) ||
                ( $user->isStateCoordinator() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() && $user->getState()->getId() === $userToVoteOn->getRegion()->getState()->getId() )
            );
        }

        /** @var SchoolAdministrator $userToVoteOn */
        if ( $userToVoteOn->isSchoolAdministrator() ) {

            $possibleStateIds = [];
            foreach($userToVoteOn->getSchools() as $school) {
                if( !$school->getState() ) { continue; }
                $possibleStateIds[] = $school->getState()->getId();
            }

            $possibleRegionIds = [];
            foreach($userToVoteOn->getSchools() as $school) {
                if( !$school->getRegion() ) { continue; }
                $possibleRegionIds[] = $school->getRegion()->getId();
            }

            return (
                ( $user->isSiteAdmin() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() ) ||
                ( $user->isStateCoordinator() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() && in_array($user->getState()->getId(), $possibleStateIds ) ) ||
                ( $user->isRegionalCoordinator() && $user->getSite() && $userToVoteOn->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() && in_array($user->getRegion()->getId(), $possibleRegionIds ) )
            );
        }

        /** @var EducatorUser $userToVoteOn */
        if ( $userToVoteOn->isEducator() || $userToVoteOn->isStudent() ) {

            $possibleSchoolIds = [];
            if($user->isSchoolAdministrator()) {
                foreach($user->getSchools() as $school) {
                    $possibleSchoolIds[] = $school->getId();
                }
            }

            return (
                ( $user->isSiteAdmin() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() ) ||
                ( $user->isStateCoordinator() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() && $user->getState()->getId() === $userToVoteOn->getSchool()->getState()->getId() ) ||
                ( $user->isRegionalCoordinator() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() && $user->getRegion()->getId() === $userToVoteOn->getSchool()->getRegion()->getId() ) ||
                ( $user->isSchoolAdministrator() && $user->getSite() && $user->getSite()->getId() === $userToVoteOn->getSite()->getId() && in_array($userToVoteOn->getSchool()->getId(), $possibleSchoolIds) )
            );
        }

        return false;
    }
}
