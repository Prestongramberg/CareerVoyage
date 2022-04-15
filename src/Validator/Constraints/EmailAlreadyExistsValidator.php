<?php
/**
 * Created by PhpStorm.
 * User: jcrawmer
 * Date: 9/27/16
 * Time: 4:55 PM
 */

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * StudentUserValidator
 *
 * Validates that a User who already exists in the database
 * who is being enrolled in a Section has the StudentUser role
 */
class EmailAlreadyExistsValidator extends ConstraintValidator
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

        $duplicateEmails = $constraint->getDuplicateEmails();

        if(in_array(strtolower($value), $duplicateEmails)) {
            $this->context->buildViolation($constraint->message2)
                          ->atPath('email')
                          ->addViolation();
        }

        $emailCache = $constraint->getEmailCache();

        if(in_array(strtolower($value), $emailCache)) {
            $this->context->buildViolation($constraint->message)
                          ->atPath('email')
                          ->addViolation();
        }

    }
}
