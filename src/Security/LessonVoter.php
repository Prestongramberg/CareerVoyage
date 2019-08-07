<?php

namespace App\Security;

use App\Entity\Company;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class LessonVoter extends Voter
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
        if (!$subject instanceof Lesson) {
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
        /** @var Lesson $lesson
         */
        $lesson = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($lesson, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Lesson $lesson, User $user)
    {
        if($user->isAdmin()) {
            return true;
        }

        if($lesson->getUser()->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}