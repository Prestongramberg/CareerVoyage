<?php

namespace App\Security;

use App\Entity\Company;
use App\Entity\Experience;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExperienceVoter extends Voter
{
    // these strings are just invented: you can use anything
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Experience) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Experience $experience */
        $experience = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($experience, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Experience $experience, User $user)
    {
        if(!$user instanceof ProfessionalUser) {
            return false;
        }

        if(!$user->getCompany()) {
            return false;
        }

        if($user->getCompany()->getId() !== $experience->getCompany()->getId()) {
            return false;
        }

        return true;
    }
}