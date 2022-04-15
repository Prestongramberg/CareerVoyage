<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UsernameAlreadyExists
 * @package App\Validator\Constraints
 * @Annotation
 */
class UsernameAlreadyExists extends Constraint
{
    public $message = 'Username already exists.';
    public $message2 = 'Duplicate username exists in this import. Please change.';

    private $usernameCache = [];

    private $duplicateUsernames = [];

    public function __construct($usernameCache, $duplicateUsernames, $options = null) {
        $this->usernameCache = $usernameCache;
        $this->duplicateUsernames = $duplicateUsernames;
        parent::__construct($options);
    }

    /**
     * @return array
     */
    public function getUsernameCache(): array
    {
        return $this->usernameCache;
    }

    /**
     * @return array
     */
    public function getDuplicateUsernames(): array
    {
        return $this->duplicateUsernames;
    }
}