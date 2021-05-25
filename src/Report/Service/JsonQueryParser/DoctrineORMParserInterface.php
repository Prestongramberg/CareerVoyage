<?php

namespace App\Report\Service\JsonQueryParser;

use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use App\Report\Service\JsonQueryParserInterface;

interface DoctrineORMParserInterface extends JsonQueryParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseJsonString(string $jsonString, string $entityClassName, array $sortColumns = null): ParsedRuleGroup;
}