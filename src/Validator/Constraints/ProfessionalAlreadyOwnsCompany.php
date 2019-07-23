<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ProfessionalAlreadyOwnsCompany extends Constraint
{
    public $message = 'You already own a company!';
    public $message2 = 'You must be a professional to create a company!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}