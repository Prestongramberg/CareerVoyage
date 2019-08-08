<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CSVColumnNamesMatch extends Constraint
{
    /**
     * @var array
     */
    public $types;

    /**
     * CSVColumnNamesMatchValidator constructor.
     * @param array $types
     * @param array $options
     */
    public function __construct($types, $options = [])
    {
        $this->types = $types;
        parent::__construct($options);
    }

    public $message = 'The CSV column names must match the example template below.';
}