<?php

namespace App\Report\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\CsvEncoder;

/**
 * Class ValueEncoder
 * @package App\Report\Serializer\Encoder
 */
class ValueEncoder extends CsvEncoder
{
    /**
     * @param array $defaultContext
     */
    public function __construct($defaultContext = [], string $enclosure = '"', string $escapeChar = '', string $keySeparator = '.', bool $escapeFormulas = false)
    {
        parent::__construct($defaultContext, $enclosure,$escapeChar, $keySeparator, $escapeFormulas);
    }

    /**
     * {@inheritdoc}
     */
    public function encode($rows, $format, array $context = [])
    {
        // We want arrays such as radio, select, checkbox, etc to be
        // stored in the same column pipe delimited
        foreach($rows as &$row) {
            foreach($row as $column => &$value) {
                if(is_array($value)) {
                    $is_multi = false;
                    foreach($value as $k => $v) {
                        if (!is_numeric($k) || is_array($v)) {
                            $is_multi = true;
                            break;
                        }
                    }
                    
                    if ($is_multi) {
                        $row[$column] = json_encode($value);
                    } else {
                        // todo Array to string conversion if $value is multidimensional (e.g. column is json type)
                        // todo even if it's flat, but associative, we probably want the keys.. 
                        $row[$column] = implode("|", $value);
                    }
                }

                if($value instanceof \DateTime) {
                    $row[$column] = $value->format("m/d/Y h:i:s A");
                }

            }
        }

        return parent::encode($rows, $format, $context);
    }
}