<?php

namespace App\Security;

use App\Entity\ProfessionalUser;
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

        // you know $subject is a Post object, thanks to supports
        /** @var ProfessionalUser $professionalUser
         */
        $professionalUser = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($professionalUser, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(ProfessionalUser $professionalUser, User $user)
    {
        return $user->getId() === $professionalUser->getId();
    }
}