<?php

namespace WebSK\Cache\Engines;

use WebSK\Utils\Assert;
use WebSK\Cache\CacheServerSettings;

/**
 * Class Memcache
 * @package WebSK\Cache\Engines
 */
class Memcache implements CacheEngineInterface
{
    use CacheKeyTrait;

    const COMPRESS_THRESHOLD_VALUE = 5000;
    const COMPRESS_THRESHOLD_MIN_SAVING = 0.2;

    /** @var CacheServerSettings[] */
    protected $cache_server_settings_arr = [];
    /** @var \Memcache */
    protected $connection;
    /** @var string */
    protected $cache_key_prefix = '';

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
    public function set(string $key, $value, int $ttl_sec = 0): bool
    {
        Assert::assert($ttl_sec >= 0);
        Assert::assert($ttl_sec < 60 * 60 * 30 * 24);

        $connection_obj = $this->getConnectionObj();
        if (!$connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        $result = $connection_obj->set($full_key, $value, MEMCACHE_COMPRESSED, $ttl_sec);

        return $result;
    }

    /** @inheritdoc */
    public function get(string $key)
    {
        $connection_obj = $this->getConnectionObj();
        if (!$connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        $result = $connection_obj->get($full_key);

        return $result;
    }

    /** @inheritdoc */
    public function delete(string $key): bool
    {
        $connection_obj = $this->getConnectionObj();
        if (!$connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        $result = $connection_obj->delete($full_key);

        return $result;
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
            Assert::assert($this->connection->addServer($server_settings_obj->getHost(), $server_settings_obj->getPort()));
            $this->connection->setCompressThreshold(self::COMPRESS_THRESHOLD_VALUE, self::COMPRESS_THRESHOLD_MIN_SAVING);
        }

        return $this->connection;
    }
}
