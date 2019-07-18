<?php

namespace App\Security;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CompanyPhotoVoter extends Voter
{
    // these strings are just invented: you can use anything
    const DELETE = 'delete';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::DELETE])) {
            return false;
        }

        // only vote on Company objects inside this voter
        if (!$subject instanceof CompanyPhoto) {
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
        /** @var CompanyPhoto $companyPhoto
         */
        $companyPhoto = $subject;

        switch ($attribute) {
            case self::DELETE:
                return $this->canDelete($companyPhoto, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canDelete(CompanyPhoto $companyPhoto, User $user)
    {
        if(!$user instanceof ProfessionalUser) {
            return false;
        }

        if(!$user->getCompany()) {
            return false;
        }

        if($user->getCompany()->getId() !== $companyPhoto->getCompany()->getId()) {
            return false;
        }

        return true;
    }
}