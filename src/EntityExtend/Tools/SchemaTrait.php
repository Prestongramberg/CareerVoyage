<?php

namespace App\EntityExtend\Tools;

trait SchemaTrait
{
    /**
     * Unfortunately due a poor design of the Doctrine\ORM\Tools\SchemaTool::getSchemaFromMetadata
     * we have to use "class_alias" to replace "Doctrine\DBAL\Schema\Visitor\RemoveNamespacedAssets"
     * with "Oro\Bundle\EntityExtendBundle\Tools\ExtendSchemaUpdateRemoveNamespacedAssets".
     */
    public function overrideRemoveNamespacedAssets()
    {
        if (!class_exists('Doctrine\DBAL\Schema\Visitor\RemoveNamespacedAssets', false)) {
            class_alias(
                'App\EntityExtend\Tools\ExtendSchemaUpdateRemoveNamespacedAssets',
                'Doctrine\DBAL\Schema\Visitor\RemoveNamespacedAssets'
            );
        }
    }

    /**
     * to disable automatic rename of autogenerated indices
     * we have to use "class_alias" to replace "Doctrine\DBAL\Schema\SchemaDiff"
     * with "Oro\Bundle\MigrationBundle\Migration\Schema\SchemaDiff"
     */
    public function overrideSchemaDiff()
    {
        if (!class_exists('Doctrine\DBAL\Schema\SchemaDiff', false)) {
            class_alias(
                'App\EntityExtend\Tools\SchemaDiff',
                'Doctrine\DBAL\Schema\SchemaDiff'
            );
        }
    }
}