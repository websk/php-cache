<?php

namespace WebSK\Cache;

use WebSK\Slim\Container;

/**
 * Class CacheWrapper
 * @package WebSK\Skif
 */
class CacheWrapper
{
    /** @var array */
    protected static $storage_arr = [];

    /**
     * @return CacheService
     */
    public static function getCacheService()
    {
        $container = Container::self();

        /** @var CacheService $cache_service */
        return CacheServiceProvider::getCacheService($container);
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public static function get($key)
    {
        return self::getCacheService()->get($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public static function delete($key)
    {
        return self::getCacheService()->delete($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expire
     * @return bool
     * @throws \Exception
     */
    public static function set($key, $value, $expire = 0)
    {
        return self::getCacheService()->set($key, $value, $expire);
    }

    /**
     * Обновляет время жизни кеша
     * @param $cache_key
     * @param $expire
     * @throws \Exception
     */
    public static function updateExpireByCacheKey($cache_key, $expire)
    {
        $cached = self::get($cache_key);
        if ($cached !== false) {
            self::set($cache_key, $cached, $expire);
        }
    }
}
