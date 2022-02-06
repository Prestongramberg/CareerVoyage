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
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    public $message = 'Email already exists.';

    /**
     * EmailAlreadyExists constructor.
     *
     * @param array $options
     * @param User  $user
     */
    public function __construct($options = null, User $user = null)
    {
        $this->user = $user;

        parent::__construct($options);
    }

    public function getUser()
    {
        return $this->user;
    }
}