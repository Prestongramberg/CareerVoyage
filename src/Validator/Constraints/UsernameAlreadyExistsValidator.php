<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * UsernameAlreadyExistsValidator
 */
class UsernameAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @param            $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        $duplicateUsernames = $constraint->getDuplicateUsernames();

        if(in_array($value, $duplicateUsernames)) {
            $this->context->buildViolation($constraint->message2)
                          ->atPath('username')
                          ->addViolation();
        }

        $usernameCache = $constraint->getUsernameCache();

        if(in_array($value, $usernameCache)) {
            $this->context->buildViolation($constraint->message)
                          ->atPath('username')
                          ->addViolation();
        }
    }
}
