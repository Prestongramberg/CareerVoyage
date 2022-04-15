<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use Symfony\Component\Validator\Constraint;

/**
 * Class EmailAlreadyExists
 * @package App\Validator\Constraints
 * @Annotation
 */
class EmailAlreadyExists extends Constraint
{
    public $message = 'Email already exists.';
    public $message2 = 'Duplicate email exists in this import. Please change.';

    private $emailCache = [];
    private $duplicateEmails = [];

    public function __construct($emailCache, $duplicateEmails, $options = null) {
        $this->emailCache = $emailCache;
        $this->duplicateEmails = $duplicateEmails;
        parent::__construct($options);
    }

    /**
     * @return array
     */
    public function getEmailCache(): array
    {
        return $this->emailCache;
    }

    /**
     * @return array
     */
    public function getDuplicateEmails(): array
    {
        return $this->duplicateEmails;
    }
}