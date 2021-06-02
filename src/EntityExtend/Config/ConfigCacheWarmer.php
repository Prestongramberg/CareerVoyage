<?php

namespace App\EntityExtend\Config;

class ConfigCacheWarmer
{

    /**
     * Determines whether it is needed to warm up a cache
     * for both configurable and non configurable entities and fields.
     */
    public const MODE_ALL = 0;

    /**
     * Determines whether it is needed to warm up a cache for configurable entities and fields only.
     * A cache for non configurable entities and fields will not be warmed up.
     */
    public const MODE_CONFIGURABLE_ONLY = 1;

    /**
     * Determines whether it is needed to warm up a cache for configurable entities only.
     * A cache for configurable fields and non configurable entities and fields will not be warmed up.
     */
    public const MODE_CONFIGURABLE_ENTITY_ONLY = 2;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var ConfigCache
     */
    private $cache;

    /**
     * ConfigCacheWarmer constructor.
     * @param ConfigManager $configManager
     * @param ConfigCache $cache
     */
    public function __construct(ConfigManager $configManager, ConfigCache $cache)
    {
        $this->configManager = $configManager;
        $this->cache = $cache;
    }


    /**
     * Warms up the configuration data cache.
     *
     * @param int $mode One of MODE_* constant
     */
    public function warmUpCache($mode = self::MODE_ALL)
    {
        /*if (!$this->configManager->isDatabaseReadyToWork()) {
            return;
        }

        $this->cache->beginBatch();
        try {
            $this->loadConfigurable($mode === self::MODE_CONFIGURABLE_ENTITY_ONLY);
            if ($mode === self::MODE_ALL) {
                // disallow to load new models
                $this->configModelLockObject->lock();
                try {
                    $this->loadNonConfigurable();
                    $this->loadVirtualFields();
                } finally {
                    $this->configModelLockObject->unlock();
                    $this->configurableEntitiesMap = null;
                }
            }
            $this->cache->saveBatch();
        } catch (\Throwable $e) {
            $this->cache->cancelBatch();
            throw $e;
        }*/
    }
}