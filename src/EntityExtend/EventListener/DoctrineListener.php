<?php

namespace App\EntityExtend\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;

/**
 * Class DoctrineListener
 * @package App\EntityExtend\EventListener
 */
class DoctrineListener
{
    /** @var Reader */
    protected $annotationReader;

    /**
     * DoctrineListener constructor.
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param LoadClassMetadataEventArgs $event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        //die("test");
        $classMetadata = $event->getClassMetadata();
        $className     = $classMetadata->getName();
    }
}