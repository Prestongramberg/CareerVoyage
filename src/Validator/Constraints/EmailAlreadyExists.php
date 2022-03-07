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

    private $emailCache = [];

    public function __construct($emailCache, $options = null) {
        $this->emailCache = $emailCache;
        parent::__construct($options);
    }

    /**
     * @return array
     */
    public function getEmailCache(): array
    {
        return $this->emailCache;
    }
}