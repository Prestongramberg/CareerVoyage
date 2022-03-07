<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class EducatorExists
 *
 * @package App\Validator\Constraints
 * @Annotation
 */
class EducatorExists extends Constraint
{

    public $message = 'An educator with this email could not be found.';

    private $educatorEmailCache = [];

    public function __construct($educatorEmailCache, $options = null) {
        $this->educatorEmailCache = $educatorEmailCache;
        parent::__construct($options);
    }

    public function getEducatorEmailCache(): array
    {
        return $this->educatorEmailCache;
    }
}