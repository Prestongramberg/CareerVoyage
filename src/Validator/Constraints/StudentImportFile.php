<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StudentImportFile extends Constraint
{
    public $message = 'The CSV column names must match the example template below.';
}