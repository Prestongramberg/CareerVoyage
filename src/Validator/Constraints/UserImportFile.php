<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UserImportFile extends Constraint
{
    public $message = 'Please upload a supported file type of csv or xlsx.';
    public $message2 = 'Error processing file. Please make sure your file has not been corrupted and has been properly saved as either a csv file or xlsx file.';

}