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

    private $usernameCache = [];

    public function __construct($usernameCache, $options = null) {
        $this->usernameCache = $usernameCache;
        parent::__construct($options);
    }

    /**
     * @return array
     */
    public function getUsernameCache(): array
    {
        return $this->usernameCache;
    }
}