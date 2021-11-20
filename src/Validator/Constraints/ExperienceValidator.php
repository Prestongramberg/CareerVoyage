<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PProfessionalAlreadyOwnsCompany
 * @package App\Validator\Constraints
 */
class ExperienceValidator extends ConstraintValidator
{

    /**
     * @var User $user
     */
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {

        $name = "josh";

        // todo validate that the start date isn't greater than the end date
        // todo validate that the street city state zipcode have been set

        /*if(!$this->user->isProfessional()) {
            $this->context->buildViolation($constraint->message2)
                ->addViolation();
        }

        if($this->user->getCompany()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }*/
    }
}