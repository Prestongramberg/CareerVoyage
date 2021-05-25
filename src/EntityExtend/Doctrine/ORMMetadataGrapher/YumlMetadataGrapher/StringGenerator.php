<?php

namespace App\EntityExtend\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher;

use App\Entity\Field;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\AnnotationParser;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\ClassStoreInterface;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGenerator\FieldGeneratorHelper;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGenerator\StringGeneratorHelper;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGenerator\StringGeneratorHelperInterface;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGenerator\VisitedAssociationLogger;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGenerator\VisitedAssociationLoggerInterface;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGeneratorInterface;

/**
 * Class StringGenerator
 *
 * @package App\EntityExtend\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher
 */
class StringGenerator implements StringGeneratorInterface
{

    /**
     * @var array
     */
    private $classStrings;

    /**
     * @var StringGeneratorHelperInterface
     */
    private $stringHelper;


    /**
     * @var ClassStoreInterface
     */
    private $classStore;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var VisitedAssociationLoggerInterface
     */
    private $associationLogger;

    /**
     * @var AnnotationParser
     */
    private $annotationParser;

    /**
     * @var bool
     */
    private $showFieldsDescription = false;

    /**
     * @param ClassStoreInterface    $classStore
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ClassStoreInterface $classStore, EntityManagerInterface $entityManager)
    {
        $this->classStore = $classStore;
        $this->entityManager = $entityManager;
        $this->associationLogger = new VisitedAssociationLogger();
        $this->stringHelper = new StringGeneratorHelper();
        $this->annotationParser = new AnnotationParser();
    }

    /**
     * @param bool $showDescription
     * @return $this
     */
    public function setShowFieldsDescription($showDescription)
    {
        $this->showFieldsDescription = $showDescription;

        return $this;
    }

    /**
     * @return VisitedAssociationLoggerInterface
     */
    public function getAssociationLogger()
    {
        return $this->associationLogger;
    }

    /**
     * Build the string representing the single graph item
     *
     * @param ClassMetadata $class
     * @return string
     */
    public function getClassString(ClassMetadata $class)
    {
        $className = $class->getName();

        if (!isset($this->classStrings[$className])) {
            $this->associationLogger->visitAssociation($className);

            $parentFields = $this->getParentFields($class);
            $fields       = $this->getClassFields($class, $parentFields, $this->showFieldsDescription);


            $methods = $this->annotationParser->getClassMethodsAnnotations($className);

            $this->classStrings[$className] = $this->stringHelper->getClassText($className, $fields, $methods);
        }

        return $this->classStrings[$className];
    }

    /**
     * Recursive function to get all fields in inheritance
     *
     * @param ClassMetadata $class
     * @param array $fields
     * @return array
     */
    private function getParentFields(ClassMetadata $class, $fields = array())
    {
        if ($parent = $this->classStore->getParent($class)) {
            $parentFields = $parent->getFieldNames();

            foreach ($parentFields as $field) {
                if (!in_array($field, $fields)) {
                    $fields[] = $field;
                }
            }

            $fields = $this->getParentFields($parent, $fields);
        }

        return $fields;
    }

    /**
     * @param ClassMetadata $class1
     * @param string $association
     * @return string
     */
    public function getAssociationString(ClassMetadata $class1, $association)
    {
        $targetClassName  = $class1->getAssociationTargetClass($association);
        $class2           = $this->classStore->getClassByName($targetClassName);
        $isInverse        = $class1->isAssociationInverseSide($association);
        $associationCount = $this->getClassCount($class1, $association);

        if (null === $class2) {
            return $this->stringHelper->makeSingleSidedLinkString(
                $this->getClassString($class1),
                $isInverse,
                $association,
                $associationCount,
                $targetClassName
            );
        }

        $reverseAssociationName = $this->getClassReverseAssociationName($class1, $association);

        $reverseAssociationCount = 0;
        $bidirectional = $this->isBidirectional(
            $reverseAssociationName,
            $isInverse,
            $class2
        );

        if ($bidirectional) {
            $reverseAssociationCount = $this->getClassCount($class2, $reverseAssociationName);
            $bidirectional = true;
        }

        $this->associationLogger->visitAssociation($targetClassName, $reverseAssociationName);

        return $this->stringHelper->makeDoubleSidedLinkString(
            $this->getClassString($class1),
            $this->getClassString($class2),
            $bidirectional,
            $isInverse,
            $reverseAssociationName,
            $reverseAssociationCount,
            $association,
            $associationCount
        );
    }




    /**
     * @param boolean $isInverse
     * @param string|null $reverseAssociationName
     * @param ClassMetadata $class2
     * @return bool
     */
    private function isBidirectional(
        $reverseAssociationName,
        $isInverse,
        ClassMetadata $class2
    ) {
        return null !== $reverseAssociationName
            && ($isInverse || $class2->isAssociationInverseSide($reverseAssociationName));
    }

    /**
     * @param ClassMetadata $class
     * @param string $association
     * @return int
     */
    private function getClassCount(ClassMetadata $class, $association)
    {
        return $class->isCollectionValuedAssociation($association) ? 2 : 1;
    }

    /**
     * @param ClassMetadata $class
     * @param array $parentFields
     * @param bool $DisplayAttributesDetails
     * @return array
     */
    private function getClassFields(ClassMetadata $class, $parentFields, $DisplayAttributesDetails = false)
    {
        $fields = array();

        //echo $class->getName();

        if (!$this->annotationParser->getClassHidesAttributes($class->getName())) {
            $hiddenFields = $this->annotationParser->getHiddenAttributes($class->getName());

            foreach ($class->getFieldNames() as $fieldName) {
                if (in_array($fieldName, $parentFields) || in_array($fieldName, $hiddenFields)) {
                    continue;
                }

                $DisplayAttributesDetails = $this->checkDisplayAnnotations(
                    $class->getName(),
                    $DisplayAttributesDetails
                );

                if (preg_match('/^field(\d+)$/', $fieldName, $m)) {
                    /** @var Field $field */
                    if ($field = $this->entityManager->getRepository(Field::class)->find($m[1])) {

                        $fields[] = $class->isIdentifier($fieldName) ?
                            '+' . $this->makeFieldName($class, $fieldName, $DisplayAttributesDetails) :
                            $this->makeFieldName($class, $fieldName, $DisplayAttributesDetails, $field->getLabel());

                        continue;
                    }
                }

                $fields[] = $class->isIdentifier($fieldName) ?
                    '+' . $this->makeFieldName($class, $fieldName, $DisplayAttributesDetails) :
                    $this->makeFieldName($class, $fieldName, $DisplayAttributesDetails);
            }
        }

       /* dd($fields);

        die();*/

        return $fields;
    }

    /**
     * Returns the $class2 association name for $class1 if reverse related (or null if not)
     *
     * @param ClassMetadata $class1
     * @param string $association
     *
     * @return string|null
     */
    private function getClassReverseAssociationName(ClassMetadata $class1, $association)
    {
        /**
         * @var ClassMetadataInfo $class1
         */
        if ($class1->getAssociationMapping($association)['isOwningSide']) {
            return $class1->getAssociationMapping($association)['inversedBy'];
        }

        return $class1->getAssociationMapping($association)['mappedBy'];
    }

    /**
     * @param ClassMetadata $class
     * @param string        $fieldName
     * @param boolean       $showTypes
     *
     * @param null          $fieldLabel
     *
     * @return string
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function makeFieldName(ClassMetadata $class, $fieldName, $showTypes, $fieldLabel = null)
    {
        if ($showTypes) {
            $helper = new FieldGeneratorHelper();

            if($fieldLabel) {
                return str_replace($fieldName, $fieldLabel, $helper->getFullField($class, $fieldName));
            }
        }

        return $fieldName;
    }

    /**
     * @param string $className
     * @param boolean $DisplayAttributesDetails
     * @return bool
     */
    private function checkDisplayAnnotations($className, $DisplayAttributesDetails)
    {
        $showParams = $this->annotationParser->getClassDisplay($className);
        if ($DisplayAttributesDetails && $showParams == 'hide') {
            return false;
        } elseif (!$DisplayAttributesDetails && $showParams == 'show') {
            return true;
        }

        return $DisplayAttributesDetails;
    }
}