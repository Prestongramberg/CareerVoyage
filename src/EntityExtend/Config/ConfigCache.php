<?php

namespace App\EntityExtend\Config;

use App\EntityExtend\Cache\ConfigCacheChain;
use App\EntityExtend\Cache\ConfigModelCacheChain;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Cache\DoctrineProvider;
use App\EntityExtend\Cache\MemoryCacheChain;

class ConfigCache
{
    /** @var ConfigCacheChain */
    private $cache;

    /** @var ConfigModelCacheChain */
    private $modelCache;

    /**
     * ConfigCache constructor.
     * @param ConfigCacheChain $cache
     * @param ConfigModelCacheChain $modelCache
     */
    public function __construct(ConfigCacheChain $cache, ConfigModelCacheChain $modelCache)
    {
        $this->cache = $cache;
        $this->modelCache = $modelCache;
    }

    /**
     * Deletes all cached configs.
     *
     * @param bool $localCacheOnly Whether data should be deleted only from memory cache
     */
    public function deleteAll($localCacheOnly = false)
    {
        $this->deleteAllConfigurable($localCacheOnly);
        $this->deleteAllConfigs($localCacheOnly);
    }

    /**
     * Deletes cache entries for all configs.
     *
     * @param bool $localCacheOnly Whether data should be deleted only from memory cache
     */
    public function deleteAllConfigs($localCacheOnly = false)
    {
        $this->entities = [];
        $this->fields = [];
        $this->listEntities = null;
        $this->listFields = [];

        if (!$localCacheOnly) {
            $this->cache->deleteAll();
        }
    }

    /**
     * Deletes cached "configurable" flags for all configs.
     *
     * @param bool $localCacheOnly Whether data should be deleted only from memory cache
     */
    public function deleteAllConfigurable($localCacheOnly = false)
    {

        $this->configurableEntities = [];
        $this->configurableFields = [];

        if (!$localCacheOnly) {
            $this->modelCache->deleteAll();
        }
    }
}