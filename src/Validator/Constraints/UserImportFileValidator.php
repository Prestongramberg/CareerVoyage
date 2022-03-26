<?php

namespace App\Validator\Constraints;

use App\Service\PhpSpreadsheetHelper;
use App\Service\UploaderHelper;
use Box\Spout\Reader\Exception\SharedStringNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class UserImportFileValidator
 *
 * @package App\Validator\Constraints
 */
class UserImportFileValidator extends ConstraintValidator
{

    /**
     * @var UploaderHelper
     */
    private $uploadHelper;

    /**
     * @var PhpSpreadsheetHelper;
     */
    private $phpSpreadsheetHelper;

    /**
     * @param  \App\Service\UploaderHelper        $uploadHelper
     * @param  \App\Service\PhpSpreadsheetHelper  $phpSpreadsheetHelper
     */
    public function __construct(UploaderHelper $uploadHelper, PhpSpreadsheetHelper $phpSpreadsheetHelper)
    {
        $this->uploadHelper         = $uploadHelper;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
    }


    /**
     * @param              $value
     * @param  Constraint  $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        if(!$value instanceof UploadedFile) {
            return;
        }

        $fileExtension = $this->uploadHelper->guessExtension($value);

        if(!in_array($fileExtension, ['csv', 'xlsx'])) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
            return;
        }

        try {
            // let's see if we are able to read the file at all and if not let's throw an error
            $rows = $this->phpSpreadsheetHelper->getAllRows($value);
        } catch (\Exception $exception) {
            $this->context->buildViolation($constraint->message2)
                          ->addViolation();
        }
    }

}