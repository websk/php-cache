<?php

namespace WebSK\Cache\Engines;

/**
 * Interface CacheEngineInterface
 * @package WebSK\Cache\Engines
 */
interface CacheEngineInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl_sec
     * @return bool
     */
    public function set(string $key, mixed $value, int $ttl_sec = 0): bool;

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
}
