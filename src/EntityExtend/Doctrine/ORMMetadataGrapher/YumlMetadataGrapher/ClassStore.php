<?php

namespace App\EntityExtend\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\ClassStoreInterface;

/**
 * Utility to generate yUML compatible strings from metadata graphs
 */
class ClassStore implements ClassStoreInterface
{

    /**
     * Indexed array of ClassMetadata and options
     *
     * @var array
     */
    private $indexedClasses = array();

    /**
     * store metadata in an associated array to get classes
     * faster into $this->getClassByName()
     *
     * @param ClassMetadata[] $metadata
     */
    public function __construct($metadata)
    {
        $this->indexClasses($metadata);
    }

    /**
     * Retrieve a class metadata instance by name from the given array
     *
     * @param   string      $className
     *
     * @return  ClassMetadata|null
     */
    public function getClassByName($className)
    {
        $classMap = $this->getClassMap($this->splitClassName($className)) . "[\"__class\"]";
        $return = null;

        eval(
            "if (isset(\$this->indexedClasses$classMap)) {"
            . " \$return = \$this->indexedClasses$classMap;"
            . "}"
        );

        return $return;
    }

    /**
     * Retrieve a class metadata's parent class metadata
     *
     * @param ClassMetadata   $class
     *
     * @return ClassMetadata|null
     */
    public function getParent(ClassMetadata $class)
    {
        $className = $class->getName();
        if (!class_exists($className) || (!$parent = get_parent_class($className))) {
            return null;
        }

        return $this->getClassByName($parent);
    }

    /**
     * @return array
     */
    public function getIndexedClasses()
    {
        return $this->indexedClasses;
    }

    /**
     * @param string $className
     * @return string
     */
    public function getClassColor($className)
    {
        $splitName = $this->splitClassName($className);
        $color = null;

        do {
            $colorMap = $this->getClassMap($splitName) . "[\"__color\"]";

            eval(
                "if (isset(\$this->indexedClasses$colorMap)) {"
                . "\$color = \$this->indexedClasses$colorMap;"
                . "}"
            );

            unset($splitName[count($splitName) - 1]);
        } while (null === $color && !empty($splitName));

        return $color;
    }

    /**
     * @param array $colors
     */
    public function storeColors($colors)
    {
        foreach ($colors as $namespace => $color) {
            $this->storeColor($namespace, $color);
        }
    }

    /**
     * @param array $classSplit
     * @return string
     */
    private function getClassMap($classSplit)
    {
        return "[\"" . implode("\"][\"", $classSplit) . "\"]";
    }

    /**
     * @param ClassMetadata[] $metadata
     */
    private function indexClasses($metadata)
    {
        $excludedClasses = [
            'App\Entity\Action'
        ];

        foreach ($metadata as $class) {

           /* if(in_array($class->getName(), $excludedClasses, true)) {
                continue;
            }*/

            $this->indexClass($class);
        }
    }

    /**
     * @param ClassMetadata $class
     */
    private function indexClass(ClassMetadata $class)
    {
        $this->checkIndexAlreadyExists($class->getName());

        $classMap = $this->getClassMap($this->splitClassName($class->getName())) . "[\"__class\"]";

        eval(
        "\$this->indexedClasses$classMap = \$class;"
        );
    }

    /**
     * @param string $className
     * @return array
     */
    private function splitClassName($className)
    {
        return explode('\\', $className);
    }

    /**
     * @param string $className
     */
    private function checkIndexAlreadyExists($className)
    {
        $namespaces = $this->splitClassName($className);

        $tmpArrayMap = "";

        foreach ($namespaces as $namespace) {
            $tmpArrayMap .= "[\"$namespace\"]";
            eval("if (!isset(\$this->indexedClasses$tmpArrayMap)) "
                . "{\$this->indexedClasses$tmpArrayMap = array(\"__class\" => null, \"__color\" => null);}");
        }
    }

    /**
     * @param string $namespace
     * @param string $color
     */
    private function storeColor($namespace, $color)
    {
        $this->checkIndexAlreadyExists($namespace);

        $colorMap = $this->getClassMap($this->splitClassName($namespace)) . "[\"__color\"]";

        eval(
        "\$this->indexedClasses$colorMap = \"$color\";"
        );
    }
}
