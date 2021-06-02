<?php

namespace App\EntityExtend\Doctrine;

use FL\QBJSParser\Parser\Doctrine\StringManipulator;

abstract class SelectPartialParser
{
    const OBJECT_WORD = 'object';

    final private function __construct()
    {
    }

    /**
     * @param array $fieldPrefixesToClasses
     *
     * @param array $columns
     * @return string
     */
    final public static function parse(array $fieldPrefixesToClasses = [], array $columns = []): string
    {
        $prepared = [];
        
        
        foreach($columns as $index => $column) {
            $field = json_decode($column['field'], true);
            
            $expression = null;
            
            if (preg_match('/(.*)JSON_ATTR\((.+),(.+)\)$/', $field['column'], $m)) {
                // (a) Special "JSON_ATTR|" prefixed columns are just helpers to extract simple values from JSON columns
                //     e.g. self.JSON_ATTR(fields,email)
                $col_name = self::getPrefixedColumnName(join([$m[1], $m[2]]));
                $attr_name = $m[3];
                $field['column'] = $col_name;
                $expression = sprintf("JSON_UNQUOTE(JSON_EXTRACT(%s, '$.\"%s\"'))", $col_name, $attr_name);
            } else if (preg_match('/^(.+) AS (.*)$/i', $column['name'], $m)) {
                // (b) raw SQL
                // e.g. CONCAT('S', JSON_UNQUOTE(JSON_EXTRACT(col_2, '$."js-staffer-meeting-count-value"'))) AS s-value
                // The reference points to the underlying column and not a 2nd expression, if present.
                $expression = $m[1];
            }
            
            $prepared[] = [
                'expression' => $expression,
                'column' => self::getPrefixedColumnName($field['column']),
                'alias' => sprintf('col_%d', $index) // ignore user alias in actual DQL
            ];
        }
        
        // substitute column references
        foreach($prepared as $i => $row) {
            if ($row['expression']) {
                foreach($prepared as $i2 => $row2) {
                    $prepared[$i]['expression'] = str_replace('col_'.$i2, $row2['column'], $prepared[$i]['expression']);
                }
            }
        }
            
        $selectStatement = sprintf('SELECT %s ', join(', ', array_map(function($e) { 
            return sprintf("%s AS %s", 
            $e['expression'] ?? $e['column'], 
            $e['alias']
        );
        }, $prepared)));
        
        return $selectStatement;
    }
    
    private static function getPrefixedColumnName($string) {
        return preg_replace('/\.(?=.*\.)/', '_', sprintf(
            '%s.%s',
            self::OBJECT_WORD,
            $string
        ));;
    }
}