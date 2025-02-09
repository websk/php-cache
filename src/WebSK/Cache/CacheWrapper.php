<?php

namespace WebSK\Cache;


use Psr\Container\ContainerInterface;

/**
 * Class CacheWrapper
 * @package WebSK\Skif
 */
class CacheWrapper
{

    protected static ContainerInterface $container;

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * @return CacheService
     */
    public static function getCacheService(): CacheService
    {
        /** @var CacheService $cache_service */
        return CacheServiceProvider::getCacheService(self::$container);
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function get($key): mixed
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
    public static function set(string $key, $value, int $expire = 0): bool
    {
        return self::getCacheService()->set($key, $value, $expire);
    }

    /**
     * Обновляет время жизни кеша
     * @param string $cache_key
     * @param int $expire
     * @throws \Exception
     */
    public static function updateExpireByCacheKey(string $cache_key, int $expire): void
    {
        $cached = self::get($cache_key);
        if ($cached !== false) {
            self::set($cache_key, $cached, $expire);
        }
    }
}
