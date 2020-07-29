<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UsernameInvalid extends Constraint
{
    public $message = 'Username cannot contain the @ character!';

}