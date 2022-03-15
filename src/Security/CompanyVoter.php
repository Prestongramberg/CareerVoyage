<?php

namespace App\Security;

use App\Entity\Company;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CompanyVoter extends Voter
{
    // these strings are just invented: you can use anything
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        // only vote on Company objects inside this voter
        if (!$subject instanceof Company) {
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

        // you know $subject is a Post object, thanks to supports
        /** @var Company $company
         */
        $company = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($company, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Company $company, User $user)
    {
        if($user->isAdmin() || $user->isRegionalCoordinator() || $user->isSiteAdmin()) {
            return true;
        }

        if($company->getOwner() && $company->getOwner()->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}