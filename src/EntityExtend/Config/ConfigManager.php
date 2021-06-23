<?php

namespace App\EntityExtend\Config;

use Doctrine\ORM\EntityManager;
use Metadata\MetadataFactory;

class ConfigManager
{

    /** @var ConfigCache */
    protected $cache;

    /**
     * ConfigManager constructor.
     * @param ConfigCache $cache
     */
    public function __construct(ConfigCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Removes all entries from all used caches, completely.
     */
    public function flushAllCaches()
    {
        /**
         * The Doctrine cache provider has two methods to clear the cache:
         *  deleteAll - Deletes all cache entries in the current cache namespace.
         *  flushAll - Flushes all cache entries, globally.
         * Actually deleteAll method does not remove cached entries, it just increase cache version. The flushAll
         * deletes all cached entries, but it does it for all namespaces.
         * The problem is that we use the same cache, but for different caches we use different namespaces.
         * E.g. we use entity_aliases namespace for entity alias cache and entity_config namespace for
         * entity config cache. But if a developer call flushAll method for any of these cache all cached entries
         * from all caches will be removed
         */
        $this->cache->deleteAll();
    }
}