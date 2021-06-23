<?php

namespace App\EntityExtend\Tools;

use App\EntityExtend\Config\ExtendHelper;
use App\EntityExtend\Tools\GeneratorExtensions\AbstractEntityGeneratorExtension;
use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use Symfony\Component\Yaml\Yaml;
use App\EntityExtend\Tools\GeneratorExtensions\ExtendEntityGeneratorExtension;

/**
 * Builds proxy classes and ORM mapping for extended entities.
 */
class EntityGenerator
{
    /** @var string */
    private $cacheDir;

    /** @var string */
    private $entityCacheDir;

    /** @var iterable|AbstractEntityGeneratorExtension[] */
    private $extensions;

    /**
     * @var ExtendEntityGeneratorExtension
     */
    private $entityGeneratorExtension;

    /**
     * @param string $cacheDir
     * @param ExtendEntityGeneratorExtension $entityGeneratorExtension
     */
    public function __construct(string $cacheDir, ExtendEntityGeneratorExtension $entityGeneratorExtension)
    {
        $this->setCacheDir($cacheDir);
        $this->entityGeneratorExtension = $entityGeneratorExtension;
    }

    /**
     * Gets the cache directory
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Sets the cache directory
     *
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
        $this->entityCacheDir = ExtendClassLoadingUtils::getEntityCacheDir($cacheDir);
    }

    /**
     * Generates extended entities
     *
     * @param array $schemas
     */
    public function generate(array $schemas)
    {
        ExtendClassLoadingUtils::ensureDirExists($this->entityCacheDir);

        // let's make sure we create the schema files for parent classes first
        // so we do not get an error when creating the child classes that extend from them
        usort($schemas, function ($a, $b){
            if($a['childClass'] === $b['childClass']) {
                return 0;
            }
            return $a['childClass'] > $b['childClass'] ? 1 : -1;
        });

        $aliases = [];
        foreach ($schemas as $schema) {
            $this->generateSchemaFiles($schema);
            if ($schema['type'] === 'Extend') {
                $aliases[$schema['entity']] = $schema['parent'];
            }
        }

        // write PHP class aliases to the file
        file_put_contents(
            ExtendClassLoadingUtils::getAliasesPath($this->cacheDir),
            serialize($aliases)
        );
    }

    /**
     * Generate php and yml files for schema
     *
     * @param array $schema
     */
    public function generateSchemaFiles(array $schema)
    {
        // generate PHP code
        $class = PhpClass::create($schema['entity']);
        if ($schema['doctrine'][$schema['entity']]['type'] === 'mappedSuperclass') {
            $class->setAbstract(true);
        }

       /* foreach ($this->extensions as $extension) {
            if ($extension->supports($schema)) {
                $extension->generate($schema, $class);
            }
        }*/

        if ($this->entityGeneratorExtension->supports($schema)) {
            $this->entityGeneratorExtension->generate($schema, $class);
        }

        $className = ExtendHelper::getShortClassName($schema['entity']);

        // write PHP class to the file
        $strategy = new DefaultGeneratorStrategy();

        $organizationNamespace = strtoupper($schema['organization_slug']);

        if (!is_dir($this->entityCacheDir . DIRECTORY_SEPARATOR . $organizationNamespace)) {
            // dir doesn't exist, make it
            if (!mkdir(
                    $concurrentDirectory = $this->entityCacheDir.DIRECTORY_SEPARATOR.$organizationNamespace
                ) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $fileName = $this->entityCacheDir . DIRECTORY_SEPARATOR . $organizationNamespace . DIRECTORY_SEPARATOR . $className . '.php';
        file_put_contents($fileName, "<?php\n\n" . $strategy->generate($class));
        clearstatcache(true, $fileName);
        // write doctrine metadata in separate yaml file
        file_put_contents(
            $this->entityCacheDir . DIRECTORY_SEPARATOR . $organizationNamespace . DIRECTORY_SEPARATOR . $className . '.orm.yml',
            Yaml::dump($schema['doctrine'], 5)
        );

    }
}