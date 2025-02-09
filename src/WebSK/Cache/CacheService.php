<?php

namespace WebSK\Cache;

use WebSK\Cache\Engines\CacheEngineInterface;

/**
 * Class CacheService
 * @package WebSK\Cache
 */
class CacheService
{
    protected CacheEngineInterface $cache_engine;

    protected array $storage_arr = [];

    /**
     * CacheService constructor.
     * @param CacheEngineInterface $cache_engine
     */
    public function __construct(CacheEngineInterface $cache_engine)
    {
        $this->cache_engine = $cache_engine;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl_sec
     * @return bool
     */
    public function set(string $key, mixed $value, int $ttl_sec = 0): bool
    {
        $this->storage_arr[$key] = $value;
        return $this->cache_engine->set($key, $value, $ttl_sec);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        if (isset($this->storage_arr[$key])) {
            return $this->storage_arr[$key];
        }

        $value = $this->cache_engine->get($key);

        if ($value !== false) {
            $this->storage_arr[$key] = $value;
        }

        return $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        unset($this->storage_arr[$key]);
        return $this->cache_engine->delete($key);
    }

    public function flushStaticCache(): void
    {
        $this->storage_arr = [];
    }
}