<?php

namespace App\EntityExtend\Tools;

//use App\Entity\BuildingBlock;
//use App\Entity\Field;
use App\EntityExtend\Config\ConfigInterface;
use App\EntityExtend\Config\ConfigManager;
use App\EntityExtend\Entity\AbstractExtendEntity;
//use App\Repository\BuildingBlockRepository;
//use App\Util\ReservedKeywords;
use App\Util\StringHelper;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;

class ExtendConfigDumper
{
    //use ReservedKeywords;
    use StringHelper;

    public const ACTION_PRE_UPDATE  = 'preUpdate';
    public const ACTION_POST_UPDATE = 'postUpdate';
    public const DEFAULT_PREFIX     = 'default_';

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var EntityGenerator
     */
    private $entityGenerator;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ExtendConfigDumper constructor.
     *
     * @param ConfigManager          $configManager
     * @param EntityGenerator        $entityGenerator
     * @param string                 $cacheDir
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ConfigManager $configManager, EntityGenerator $entityGenerator, string $cacheDir,
                                EntityManagerInterface $entityManager
    ) {
        $this->configManager   = $configManager;
        $this->entityGenerator = $entityGenerator;
        $this->cacheDir        = $cacheDir;
        $this->entityManager   = $entityManager;
    }

    public function dump()
    {

        $buildingBlocks = $this->buildingBlockRepository->findBy([
            'useEntityExtendMapping' => true
        ]);


        $schemas = [];

        foreach ($buildingBlocks as $buildingBlock) {

            /**
             * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/yaml-mapping.html
             */
            $schemas[$buildingBlock->getExtendDatabaseTableName()] = [
                'type'              => 'Custom',
                'organization_slug' => $buildingBlock->getOrganization()->getSlug(),
                'entity'            => $buildingBlock->getExtendDatabaseNamespace(),
                'inherit'           => !empty($schemas[$buildingBlock->getExtendDatabaseTableName()]['inherit']) ?
                    $schemas[$buildingBlock->getExtendDatabaseTableName()]['inherit'] : AbstractExtendEntity::class,
                'childClass'        => (!empty(
                    $schemas[$buildingBlock->getExtendDatabaseTableName()]['childClass']
                    ) && $schemas[$buildingBlock->getExtendDatabaseTableName()]['childClass'] === true),
                'property'          => $buildingBlock->getPropertyMetadataForDoctrine(),
                'relation'          => $buildingBlock->getRelationPropertyMetadataForDoctrine($this->entityManager),
                'doctrine'          => [
                    $buildingBlock->getExtendDatabaseNamespace() => [
                        'type'       => 'entity',
                        'table'      => $buildingBlock->getExtendDatabaseTableName(),
                        'fields'     => $buildingBlock->getFieldMetadataForDoctrine(),
                        'oneToOne'   => $buildingBlock->getOneToOneRelationshipMetadataForDoctrine($this->entityManager),
                        'oneToMany'  => $buildingBlock->getOneToManyRelationshipMetadataForDoctrine($this->entityManager),
                        'manyToOne'  => $buildingBlock->getManyToOneRelationshipMetadataForDoctrine($this->entityManager),
                        'manyToMany' => $buildingBlock->getManyToManyRelationshipMetadataForDoctrine($this->entityManager),
                    ],
                ],
            ];


            /**
             * Discriminator mapping and table inheritance in YAML
             *
             * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/inheritance-mapping.html#association-override
             */
            $childBuildingBlocks = $this->buildingBlockRepository->findBy(
                [
                    'doctrineEntityInheritanceMapping' => $buildingBlock,
                ]
            );

            if (!empty($childBuildingBlocks)) {

                $anyChildBuildingBlocksAreActive = false;
                foreach ($childBuildingBlocks as $childBuildingBlock) {
                    if ($childBuildingBlock->getUseEntityExtendMapping()) {
                        $anyChildBuildingBlocksAreActive = true;
                        break;
                    }
                }

                if ($anyChildBuildingBlocksAreActive) {
                    $schemas[$buildingBlock->getExtendDatabaseTableName()]['doctrine']
                    [$buildingBlock->getExtendDatabaseNamespace()]['inheritanceType'] = 'JOINED';

                    $schemas[$buildingBlock->getExtendDatabaseTableName()]['doctrine']
                    [$buildingBlock->getExtendDatabaseNamespace()]['discriminatorColumn'] = [
                        'name' => 'discr',
                        'type' => 'string',
                    ];


                    $schemas[$buildingBlock->getExtendDatabaseTableName()]['doctrine']
                    [$buildingBlock->getExtendDatabaseNamespace()]['discriminatorMap']
                    [$buildingBlock->getInternalName()] = $this->upperCamelCase($this->cleanName($buildingBlock->getName()));
                }

                foreach ($childBuildingBlocks as $childBuildingBlock) {
                    if ($childBuildingBlock->getUseEntityExtendMapping() && $childBuildingBlock->getDoctrineEntityInheritanceMapping()) {

                        $schemas[$childBuildingBlock->getExtendDatabaseTableName()]['childClass'] = true;
                        $schemas[$childBuildingBlock->getExtendDatabaseTableName()]['inherit']    =
                            $buildingBlock->getRepositoryClassName();

                        $schemas[$buildingBlock->getExtendDatabaseTableName()]['doctrine']
                        [$buildingBlock->getExtendDatabaseNamespace()]['discriminatorMap']
                        [$this->camelCase($childBuildingBlock->getInternalName())] =
                            $this->upperCamelCase($this->cleanName($childBuildingBlock->getName()));
                    }
                }
            }
        }


        // Clear out the directory to remove deleted schemas/unneeded tables
        $entityCacheDir = ExtendClassLoadingUtils::getEntityCacheDir($this->cacheDir);
        if (is_dir($entityCacheDir)) {
            $filesystem                    = new Filesystem();
            $existingExtendedEntitiesFiles = array_filter(
                scandir($entityCacheDir), function ($e) {
                return !in_array($e, array ('.', '..')) && in_array(pathinfo($e, PATHINFO_EXTENSION), ['yml', 'php']);
            }
            );
            foreach ($existingExtendedEntitiesFiles as $filename) {
                $path = $entityCacheDir . DIRECTORY_SEPARATOR . $filename;
                try {
                    $filesystem->remove($path);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }


        $cacheDir = $this->entityGenerator->getCacheDir();
        if ($cacheDir === $this->cacheDir) {
            $this->entityGenerator->generate($schemas);
        } else {
            $this->entityGenerator->setCacheDir($this->cacheDir);
            try {
                $this->entityGenerator->generate($schemas);
                $this->entityGenerator->setCacheDir($cacheDir);
            } catch (\Exception $e) {
                $this->entityGenerator->setCacheDir($cacheDir);
                throw $e;
            }
        }
    }

    /**
     * TODO test passing in your schema config here and see what happens?
     *
     * Load relation data and add state of scope extend  of entity config
     *
     * @param ConfigInterface[] $extendConfigs
     * @param ConfigInterface   $entityExtendConfig
     *
     * @return mixed|null
     */
    private function getRelationDataForEntity($extendConfigs, ConfigInterface $entityExtendConfig)
    {
        $relationData = $entityExtendConfig->get('relation', false, []);

        if (is_array($relationData)) {
            foreach ($relationData as $key => &$item) {
                /** @var ConfigInterface $extendConfig */
                foreach ($extendConfigs as $extendConfig) {
                    if ($extendConfig->getId()->getClassName() === $item['target_entity']) {
                        $values        = $extendConfig->getValues();
                        $item['state'] = null;
                        if (isset($values['state'])) {
                            $item['state'] = $extendConfig->getValues();
                        }

                        break;
                    }
                }
            }
        }

        return $relationData;
    }
}