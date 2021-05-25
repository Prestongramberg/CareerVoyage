<?php

namespace App\Report\Service\JsonQueryParser;

use App\Entity\Report;
use App\Repository\FieldRepository;
use Doctrine\ORM\EntityManagerInterface;
use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Serializer\JsonDeserializer;
//use App\Report\Serializer\ReportJsonDeserializer;
use App\EntityExtend\Doctrine\DoctrineParser;

/**
 * Class DoctrineORMParser
 * @package App\Report\Service\JsonQueryParser
 */
class DoctrineORMParser implements DoctrineORMParserInterface
{
    /**
     * @var DoctrineParser[]
     */
    private $classNameToDoctrineParser = [];

    /**
     * @var JsonDeserializer
     */
    private $jsonDeserializer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FieldRepository
     */
    private $fieldRepository;

    /**
     * DoctrineORMParser constructor.
     * @param JsonDeserializer $jsonDeserializer
     * @param EntityManagerInterface $entityManager
     * @param FieldRepository $fieldRepository
     */
    public function __construct(
        JsonDeserializer $jsonDeserializer,
        EntityManagerInterface $entityManager,
        FieldRepository $fieldRepository
    ) {
        $this->jsonDeserializer = $jsonDeserializer;
        $this->entityManager = $entityManager;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @param Report $report
     * @return ParsedRuleGroup
     */
    public function parseReport(Report $report) {

        $columns = [];
        foreach($report->getReportColumns() as $reportColumn) {
            $columns[] = [
                'field' => $reportColumn->getField(),
                'name' => $reportColumn->getName()
            ];
        }

        return $this->parseJsonString($report->getRules(), $report->getEntity()->getExtendDatabaseNamespace(), $columns);
    }
    
    // walk the association
    // define all intermediary associations too
    public function registerAssociationClassFromString($string, $entityClassName, $classMetadata, &$associationClasses) {
        $parts = explode('.', $string);
        if (count($parts) > 1) {
            $curEntity = $entityClassName;
            $curMeta = $classMetadata;
            for($i = 0; $i < count($parts) - 1; $i++) {                
                $associationName = $parts[$i];
                if (array_key_exists($associationName, $curMeta->associationMappings)) {
                    $curEntity = $curMeta->associationMappings[$associationName]['targetEntity'];
                    $curMeta = $this->entityManager->getClassMetadata($curEntity);
                    $id = join('.', array_slice($parts, 0, $i + 1)); 
                    $associationClasses[$id] = $curEntity;
                }
            }
        }
    }
    
    public function getRulesRecursive($ruleGroup) : iterable {
        foreach($ruleGroup->getRules() as $rule) {
            yield $rule;
        }
        foreach($ruleGroup->getRuleGroups() as $ruleGroup) {
            yield from $this->getRulesRecursive($ruleGroup);
        }
    }

    /**
     * @param string $jsonString
     * @param string $entityClassName
     * @param array|null $columns
     * @param array|null $sortColumns
     *
     * @return ParsedRuleGroup
     */
    public function parseJsonString(string $jsonString, string $entityClassName, array $columns = null, array $sortColumns = null): ParsedRuleGroup
    {
        $classToPropertiesMapping = [];
        $classToAssociationMapping = [];
        $classToEmbeddablesPropertiesMapping = [];
        $classToEmbeddablesInsideEmbeddablesPropertiesMapping = [];
        $classToEmbeddablesAssociationMapping = [];
        $classToEmbeddablesEmbeddableMapping = [];
        $associationClasses = [];
        $properties = [];
        
        $ruleGroup = $this->jsonDeserializer->deserialize($jsonString);
        $classMetadata = $this->entityManager->getClassMetadata($entityClassName);

        // Extract association classes from selected columns
        foreach($columns as $column) {
            if ($field = json_decode($column['field'], true)) {
                $this->registerAssociationClassFromString($field['column'], $entityClassName, $classMetadata, $associationClasses);
            }
        }
        
        // Extract association classes from where (nested rules & rule groups)

        // Grab the associations and properties from the filters
        foreach($this->getRulesRecursive($ruleGroup) as $rule) {
            $fieldParts = explode(".", $rule->getField());
            $properties[$rule->getField()] = $rule->getField();
            
            if (count($fieldParts) == 1) {
                // There's no association to define!
                continue;
            }
            
            // e.g. "self.user.id"
            array_pop($fieldParts);
            
            // Goal:
            // "self" => "App\Entity\PersonRecord"
            // "self.user" => "App\Entity\User"
            $prevClassName = $entityClassName;
            for($i = 0; $i < count($fieldParts); $i++) {
                $intermediaryAssociation = array_slice($fieldParts, 0, $i + 1);
                $associationName = $intermediaryAssociation[$i];
                $associationKey = join('.', $intermediaryAssociation);
                
                if (array_key_exists($associationKey, $associationClasses)) {
                    $prevClassName = $associationClasses[$associationKey];
                    continue;
                }
                
                if (preg_match('/^field(\d+)$/', $associationName, $m)) {
                    // The association is trivial to derive in the extended namespace
                    if (!($field = $this->fieldRepository->find($m[1]))) {
                        continue;
                    }

                    if(!$field->isRelationshipField()) {
                        continue;
                    }

                    if($field->getBuildingBlockToJoinOn()->getExtendDatabaseNamespace() === $entityClassName) {
                        $extendedDatabaseName = $field->getBuildingBlock()->getExtendDatabaseNamespace();
                    } else {
                        $extendedDatabaseName = $field->getBuildingBlockToJoinOn()->getExtendDatabaseNamespace();
                    }
                } else {
                    // Otherwise, try to look up the association dynamically.
                    $meta = $this->entityManager->getClassMetadata($prevClassName);
                    if (!array_key_exists($associationName, $meta->associationMappings)) {
                        throw new \Exception(sprintf("Undefined association \"%s\" for class %s", $associationKey, $prevClassName));
                    }
                    
                    $extendedDatabaseName = $meta->associationMappings[$associationName]['targetEntity'];
                }
                
                $associationClasses[$associationKey] = $extendedDatabaseName;
                $prevClassName = $extendedDatabaseName;
            }
        }
        
        $classesAndMappings = [
            $classMetadata->getTableName() => [
                'class' => $classMetadata->rootEntityName,
                // used in the select columns
                'columns' => $columns,
                // used in the where statements
                'properties' => $properties,
                // used for joins
                'association_classes' => $associationClasses,
                // We don't use embeddables in this app so the 4 arrays below we can ignore
                // https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/tutorials/embeddables.html
                'embeddables_properties' => [],
                'embeddables_inside_embeddables_properties' => [],
                'embeddables_association_classes' => [],
                'embeddables_embeddable_classes' => []
            ]
        ];

        foreach ($classesAndMappings as $classAndMappings) {
            $className = $classAndMappings['class'];
            
            foreach ($classAndMappings['properties'] as $field => $entityProperty) {
                $classToPropertiesMapping[$className][$field] = $entityProperty ? $entityProperty : $field;
            }
            foreach ($classAndMappings['association_classes'] as $prefix => $class) {
                $classToAssociationMapping[$className][$prefix] = $class;
            }
            foreach ($classAndMappings['embeddables_properties'] as $field => $embeddableProperty) {
                $classToEmbeddablesPropertiesMapping[$className][$field] = $embeddableProperty ? $embeddableProperty : $field;
            }
            foreach ($classAndMappings['embeddables_inside_embeddables_properties'] as $field => $embeddableProperty) {
                $classToEmbeddablesInsideEmbeddablesPropertiesMapping[$className][$field] = $embeddableProperty ? $embeddableProperty : $field;
            }
            foreach ($classAndMappings['embeddables_association_classes'] as $prefix => $class) {
                $classToEmbeddablesAssociationMapping[$className][$prefix] = $class;
            }
            foreach ($classAndMappings['embeddables_embeddable_classes'] as $prefix => $class) {
                $classToEmbeddablesEmbeddableMapping[$className][$prefix] = $class;
            }
            
            $this->classNameToDoctrineParser[$className] = new DoctrineParser(
                $className,
                $classToPropertiesMapping[$className] ?? [],
                $columns,
                $classToAssociationMapping[$className] ?? [],
                $classToEmbeddablesPropertiesMapping[$className] ?? [],
                $classToEmbeddablesInsideEmbeddablesPropertiesMapping[$className] ?? [],
                $classToEmbeddablesAssociationMapping[$className] ?? [],
                $classToEmbeddablesEmbeddableMapping[$className] ?? []
            );
        }
        
        $doctrineParser = $this->newParser($entityClassName);

        return $doctrineParser->parse($this->jsonDeserializer->deserialize($jsonString), $sortColumns);
    }

    /**
     * @param string $className
     *
     * @return DoctrineParser
     *
     * @throws \DomainException
     */
    private function newParser(string $className)
    {
        if (!array_key_exists($className, $this->classNameToDoctrineParser)) {
            throw new \DomainException(sprintf('You have requested a Doctrine Parser for %s, but you have not defined a mapping for it in your configuration', $className));
        }

        return $this->classNameToDoctrineParser[$className];
    }
}