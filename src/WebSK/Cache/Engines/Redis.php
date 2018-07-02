<?php

namespace WebSK\Cache\Engines;

use Predis\Client;
use WebSK\Cache\CacheServerSettings;

class Redis implements CacheEngineInterface
{
    /** @var CacheServerSettings[] */
    protected $cache_server_settings_arr = [];
    /** @var Client */
    protected $connection;
    /** @var array */
    protected $params_arr = [];
    /** @var string */
    protected $cache_key_prefix = '';

    /**
     * Redis constructor.
     * @param CacheServerSettings[] $cache_server_settings_arr
     * @param array $params_arr
     * @param string $cache_key_prefix
     */
    public function __construct(array $cache_server_settings_arr, string $cache_key_prefix = '', array $params_arr = [])
    {
        $this->cache_server_settings_arr = $cache_server_settings_arr;
        $this->cache_key_prefix = $cache_key_prefix;
        $this->params_arr = $params_arr;
    }


    /**
     * @param $key
     * @param $value
     * @param $ttl_secs
     * @return bool
     */
    public function set($key, $value, $ttl_secs)
    {
        if ($ttl_secs == -1) {
            $ttl_secs = 60;
        }

        if ($ttl_secs < 0) {
            $ttl_secs = 0;
        }

        if ($ttl_secs == 0) {
            return true;
        }

        $redis_connection_obj = $this->getRedisConnectionObj(); // do not check result - already checked
        if (!$redis_connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        $value_ser = serialize($value);

        if ($ttl_secs > 0) {
            $mcs_result = $redis_connection_obj->setex($full_key, $ttl_secs, $value_ser);
        } else {
            $mcs_result = $redis_connection_obj->set($full_key, $value_ser);
        }

        if (!$mcs_result) {
            return false;
        }

        return true;
    }

    /**
     * returns false if key not found
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $redis_connection_obj = $this->getRedisConnectionObj();
        if (!$redis_connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        $result = $redis_connection_obj->get($full_key);

        if ($result === false) {
            return false;
        }

        $result = unserialize($result);

        return $result;
    }

    /**
     * @param $key
     * @return bool|int
     */
    public function delete($key)
    {
        $redis_connection_obj = $this->getRedisConnectionObj();
        if (!$redis_connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        return $redis_connection_obj->del($full_key);
    }

    /**
     * @return null|Client
     */
    public function getRedisConnectionObj()
    {
        $redis = null;

        if (isset($this->connection)) {
            return $this->connection;
        }

        $redis_servers = $this->cache_server_settings_arr;
        if (empty($redis_servers)) {
            return null;
        }

        $servers_arr = [];
        foreach ($redis_servers as $server_settings_obj) {
            $servers_arr[] = [
                'scheme' => 'tcp',
                'host' => $server_settings_obj->getHost(),
                'port' => $server_settings_obj->getPort()
            ];
        }

        $this->connection = new Client($servers_arr, $this->params_arr);

        return $this->connection;
    }

    /**
     * @param $key
     * @return string
     */
    public function getKey($key)
    {
        $prefix = $this->cache_key_prefix;
        if ($prefix == '') {
            $prefix = 'default';
        }

        $full_key = $prefix . '-' . $key;

        return md5($full_key);
    }
}
