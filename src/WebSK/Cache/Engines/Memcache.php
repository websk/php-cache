<?php

namespace WebSK\Cache\Engines;

use WebSK\Cache\CacheServerSettings;

/**
 * Class Memcache
 * @package WebSK\Cache\Engines
 */
class Memcache implements CacheEngineInterface
{
    use CacheKeyTrait;

    const int COMPRESS_THRESHOLD_VALUE = 5000;
    const float COMPRESS_THRESHOLD_MIN_SAVING = 0.2;

    /** @var CacheServerSettings[] */
    protected array $cache_server_settings_arr = [];

    protected ?\Memcache $connection = null;

    protected string $cache_key_prefix = '';

    /**
     * Memcache constructor.
     * @param CacheServerSettings[] $cache_server_settings_arr
     * @param string $cache_key_prefix
     */
    public function __construct(array $cache_server_settings_arr, string $cache_key_prefix = '')
    {
        $this->cache_server_settings_arr = $cache_server_settings_arr;
        $this->cache_key_prefix = $cache_key_prefix;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl_sec Expiration time of the item. If it's equal to zero, the item will never expire.
     * You can also use Unix timestamp or a number of seconds starting from current time,
     * but in the latter case the number of seconds may not exceed 2592000 (30 days).
     * @see http://php.net/manual/en/memcache.set.php
     * @return bool
     */
    public function set(string $key, mixed $value, int $ttl_sec = 0): bool
    {

        if ($ttl_sec < 0) {
            throw new \Exception(
                'ttl_sec can`t be less than 0'
            );
        }

        if ($ttl_sec >= 60 * 60 * 24 * 30) {
            throw new \Exception(
                'ttl_sec cannot be more than a 30 days'
            );
        }

        $connection_obj = $this->getConnectionObj();
        if (!$connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        $result = $connection_obj->set($full_key, $value, MEMCACHE_COMPRESSED, $ttl_sec);

        return $result;
    }

    /** @inheritdoc */
    public function get(string $key): mixed
    {
        $connection_obj = $this->getConnectionObj();
        if (!$connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);

        return $connection_obj->get($full_key);
    }

    /** @inheritdoc */
    public function delete(string $key): bool
    {
        $connection_obj = $this->getConnectionObj();
        if (!$connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);

        return $connection_obj->delete($full_key);
    }

    /**
     * @return \Memcache|null
     * @throws \Exception
     */
    public function getConnectionObj(): ?\Memcache
    {
        if ($this->connection instanceof \Memcache) {
            return $this->connection;
        }

        if (empty($this->cache_server_settings_arr)) {
            return null;
        }

        $this->connection = new \Memcache;

        foreach ($this->cache_server_settings_arr as $server_settings_obj) {
            if (!$this->connection->addServer($server_settings_obj->getHost(), $server_settings_obj->getPort())) {
                throw new \Exception(
                    'Unable to add memcache server ' . $server_settings_obj->getHost()
                );
            }

            $this->connection->setCompressThreshold(self::COMPRESS_THRESHOLD_VALUE, self::COMPRESS_THRESHOLD_MIN_SAVING);
        }

        return $this->connection;
    }
}
