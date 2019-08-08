<?php

namespace App\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class StudentImportFileValidator
 * @package App\Validator\Constraints
 */
class StudentImportFileValidator extends ConstraintValidator
{
    /**
     * @param UploadedFile $file
     * @param Constraint $constraint
     */
    public function validate($file, Constraint $constraint)
    {
        $studentCSVExpectedColumns = ['First Name', 'Last Name', 'Student Id'];

        if($file) {
            $columns = [];
            $tempPathName = $file->getRealPath();
            if (($fp = fopen($tempPathName, "r")) !== false) {

                while (($row = fgetcsv($fp, 1000, ",")) !== false) {
                    $columns = $row;
                    break;
                }
                fclose($fp);
            }

            if($studentCSVExpectedColumns != $columns) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}