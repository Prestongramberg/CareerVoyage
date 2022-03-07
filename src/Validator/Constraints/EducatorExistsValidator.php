<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * EducatorExistsValidator
 */
class EducatorExistsValidator extends ConstraintValidator
{

    /**
     * @var UserRepository
     */
    private $userRepository;


    /**
     * Constructor
     *
     * @param UserRepository $userRepository
     */
    public function __construct(

        UserRepository $userRepository

    ) {

        $this->userRepository = $userRepository;
    }

    /**
     * @param            $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {

        if (!$value) {
            return;
        }

        $educatorEmailCache = $constraint->getEducatorEmailCache();

        if(!in_array($value, $educatorEmailCache)) {
            $this->context->buildViolation($constraint->message)
                          ->atPath('educatorEmail')
                          ->addViolation();
        }

    }
}
