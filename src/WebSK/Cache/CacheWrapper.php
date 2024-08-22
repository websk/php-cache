<?php

namespace WebSK\Cache;

use WebSK\Slim\Container;

/**
 * Class CacheWrapper
 * @package WebSK\Skif
 */
class CacheWrapper
{
    protected static array $storage_arr = [];

    /**
     * @return CacheService
     */
    public static function getCacheService(): CacheService
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
    public static function delete($key): bool
    {
        return self::getCacheService()->delete($key);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expire
     * @return bool
     * @throws \Exception
     */
    public static function set(string $key, $value, int $expire = 0)
    {
        return self::getCacheService()->set($key, $value, $expire);
    }

    /**
     * Обновляет время жизни кеша
     * @param string $cache_key
     * @param int $expire
     * @throws \Exception
     */
    public static function updateExpireByCacheKey(string $cache_key, int $expire)
    {
        $cached = self::get($cache_key);
        if ($cached !== false) {
            self::set($cache_key, $cached, $expire);
        }
    }
}
