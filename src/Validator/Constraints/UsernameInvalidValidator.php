<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class UsernameInvalidValidator
 * @package App\Validator\Constraints
 */
class UsernameInvalidValidator extends ConstraintValidator
{

    /**
     * @param $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if(strpos($value, '@') !== false){
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}