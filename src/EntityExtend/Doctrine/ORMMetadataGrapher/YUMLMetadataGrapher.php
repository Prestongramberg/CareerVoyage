<?php

namespace App\EntityExtend\Doctrine\ORMMetadataGrapher;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\AnnotationParser;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\ColorManager;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\NotesManager;
use Onurb\Doctrine\ORMMetadataGrapher\YUMLMetadataGrapherInterface;
use App\EntityExtend\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGenerator;
use App\EntityExtend\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\ClassStore;

/**
 * Utility to generate yUML compatible strings from metadata graphs
 */
class YUMLMetadataGrapher implements YUMLMetadataGrapherInterface
{
    /**
     * @var ClassStore
     */
    private $classStore;

    /**
     * @var StringGenerator
     */
    private $stringGenerator;

    /**
     * @var array
     */
    private $str = array();

    /**
     * @var ColorManager
     */
    private $colorManager;

    /**
     * @var AnnotationParser
     */
    private $annotationParser;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->annotationParser = new AnnotationParser();
    }


    /**
     * Generate a yUML compatible `dsl_text` to describe a given array of entities
     *
     * @param ClassMetadata[] $metadata
     * @param boolean $showFieldsDescription
     * @param array $colors
     * @param array $notes
     * @return string
     */
    public function generateFromMetadata(
        array $metadata,
        $showFieldsDescription = false,
        $colors = array(),
        $notes = array()
    ) {

        $this->classStore = new ClassStore($metadata);
        $this->stringGenerator = new StringGenerator($this->classStore, $this->entityManager);
        $this->colorManager = new ColorManager($this->stringGenerator, $this->classStore);

        $this->stringGenerator->setShowFieldsDescription($showFieldsDescription);

        $annotations = $this->annotationParser->getAnnotations($metadata);

        $colors = array_merge($colors, $annotations['colors']);
        $notes = array_merge($notes, $annotations['notes']);

        foreach ($metadata as $class) {
            $this->writeParentAssociation($class);
            $this->dispatchStringWriter($class, $class->getAssociationNames());
        }

        $this->addColors($metadata, $colors);
        $this->addNotes($notes);

        return implode(',', $this->str);
    }

    private function addColors($metadata, $colors)
    {
        if (!empty($colors)) {
            $return = $this->colorManager->getColorStrings($metadata, $colors);
            $this->str = array_merge($this->str, $return);
        }
    }

    /**
     * @param ClassMetadata $class
     * @param $associations
     */
    private function dispatchStringWriter(ClassMetadata $class, $associations)
    {
        if (empty($associations)) {
            $this->writeSingleClass($class);
        } else {
            $this->writeClassAssociations($class, $associations);
        }
    }

    /**
     * @param ClassMetadata $class
     */
    private function writeParentAssociation(ClassMetadata $class)
    {
        if ($parent = $this->classStore->getParent($class)) {
            $this->str[] = $this->stringGenerator->getClassString($parent) . '^'
                . $this->stringGenerator->getClassString($class);
        }
    }

    /**
     * @param ClassMetadata $class
     */
    private function writeSingleClass(ClassMetadata $class)
    {
        if (!$this->stringGenerator->getAssociationLogger()->isVisitedAssociation($class->getName())) {
            $this->str[] = $this->stringGenerator->getClassString($class);
        }
    }

    /**
     * @param ClassMetadata $class
     * @param array $associations
     */
    private function writeClassAssociations(ClassMetadata $class, $associations)
    {
        $inheritanceAssociations = $this->getInheritanceAssociations($class);

        foreach ($associations as $associationName) {
            if (in_array($associationName, $inheritanceAssociations)) {
                continue;
            }

            $this->writeAssociation($class, $associationName);
        }
    }

    /**
     * @param ClassMetadata $class
     * @param string $association
     */
    private function writeAssociation(ClassMetadata $class, $association)
    {
        if ($this->stringGenerator->getAssociationLogger()
                                  ->visitAssociation($class->getName(), $association)
        ) {
            $this->str[] = $this->stringGenerator->getAssociationString($class, $association);
        }
    }

    /**
     * Recursive function to get all associations in inheritance
     *
     * @param ClassMetadata $class
     * @param array $associations
     * @return array
     */
    private function getInheritanceAssociations(ClassMetadata $class, $associations = array())
    {
        if ($parent = $this->classStore->getParent($class)) {
            foreach ($parent->getAssociationNames() as $association) {
                if (!in_array($association, $associations)) {
                    $associations[] = $association;
                }
            }
            $associations = $this->getInheritanceAssociations($parent, $associations);
        }

        return $associations;
    }

    /**
     * @param array $notes
     */
    private function addNotes($notes)
    {
        if (!empty($notes)) {
            $notesManager = new NotesManager($this->classStore, $this->stringGenerator);
            $this->str = array_merge($this->str, $notesManager->getNotesStrings($notes));
        }
    }
}