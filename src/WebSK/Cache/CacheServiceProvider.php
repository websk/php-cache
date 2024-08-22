<?php

namespace WebSK\Cache;

use Psr\Container\ContainerInterface;
use WebSK\Cache\Engines\CacheEngineInterface;

/**
 * Class CacheServiceProvider
 * @package WebSK\Cache
 */
class CacheServiceProvider
{
    const string SERVICE_CONTAINER_ID = 'cache.cache_service';
    const string SETTINGS_CONTAINER_ID = 'settings';

    /**
     * @param ContainerInterface $container
     */
    public static function register(ContainerInterface $container)
    {
        /**
         * @param ContainerInterface $container
         * @return CacheService
         */
        $container[self::SERVICE_CONTAINER_ID] = function (ContainerInterface $container) {
            $cache_config = $container[self::SETTINGS_CONTAINER_ID]['cache'];

            $cache_servers_arr = [];
            foreach ($cache_config['servers'] as $server_config) {
                $cache_servers_arr[] = new CacheServerSettings($server_config['host'], $server_config['port']);
            }

            /** @var CacheEngineInterface $cache_engine_class_name */
            $cache_engine_class_name = $cache_config['engine'];
            $cache_engine = new $cache_engine_class_name($cache_servers_arr, $cache_config['cache_key_prefix']);

            return new CacheService($cache_engine);
        };
    }

    /**
     * @param ContainerInterface $container
     * @return CacheService
     */
    public static function getCacheService(ContainerInterface $container): CacheService
    {
        return $container->get(self::SERVICE_CONTAINER_ID);
    }
}
