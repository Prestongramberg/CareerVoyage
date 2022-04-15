<?php

namespace App\Validator\Constraints;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * UsernameAlreadyExistsValidator
 */
class UsernameAlreadyExistsValidator extends ConstraintValidator
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param  \App\Repository\UserRepository  $userRepository
     */
    public function __construct(UserRepository $userRepository) { $this->userRepository = $userRepository; }

    /**
     * @param            $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        $user = $this->userRepository->loadUserByUsername($value);

        $usernameCache = $constraint->getUsernameCache();

        if($user) {
            $this->context->buildViolation($constraint->message)
                          ->atPath('username')
                          ->addViolation();
        }
    }
}
