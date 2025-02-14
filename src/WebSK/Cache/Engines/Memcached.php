<?php

namespace WebSK\Cache\Engines;

use WebSK\Cache\CacheServerSettings;

/**
 * Class Memcached
 * @package WebSK\Cache\Engines
 */
class Memcached implements CacheEngineInterface
{
    use CacheKeyTrait;

    /** @var CacheServerSettings[] */
    protected array $cache_server_settings_arr = [];

    protected ?\Memcached $connection = null;

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
     * @param int $ttl_sec Some storage commands involve sending an expiration value (relative to an item or to an operation requested by the client) to the server.
     * In all such cases, the actual value sent may either be Unix time (number of seconds since January 1, 1970, as an integer),
     * or a number of seconds starting from current time. In the latter case, this number of seconds may not exceed 60*60*24*30 (number of seconds in 30 days);
     * if the expiration value is larger than that, the server will consider it to be real Unix time value rather than an offset from current time.
     * If the expiration value is 0 (the default), the item never expires (although it may be deleted from the server to make place for other items).
     * @see http://php.net/manual/en/memcached.expiration.php
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

        return $connection_obj->set($full_key, $value, $ttl_sec);
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
     * @return \Memcached|null
     * @throws \Exception
     */
    protected function getConnectionObj(): ?\Memcached
    {
        if ($this->connection instanceof \Memcached) {
            return $this->connection;
        }

        if (empty($this->cache_server_settings_arr)) {
            return null;
        }

        $this->connection = new \Memcached();
        $this->connection->setOption(\Memcached::OPT_COMPRESSION, true);
        $this->connection->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
        $this->connection->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

        $memcached_servers_arr = [];
        foreach ($this->cache_server_settings_arr as $server_settings_obj) {
            $memcached_servers_arr[] = [$server_settings_obj->getHost(), $server_settings_obj->getPort()];
        }

        if (!$this->connection->addServers($memcached_servers_arr)) {
            throw new \Exception(
                'Unable to add memcached servers'
            );
        }

        return $this->connection;
    }
}
