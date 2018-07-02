<?php

namespace WebSK\Cache\Engines;

use Websk\Utils\Assert;
use Websk\Cache\CacheServerSettings;

class Memcache implements CacheEngineInterface
{
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
     * @param $key
     * @param $value
     * @param $exp
     * @return bool
     */
    public function set($key, $value, $exp)
    {
        if ($exp == -1) {
            $exp = 60;
        }

        if ($exp > 0) {
            if ($exp > 2592000) { // не добавляем тайм для мелких значений, чтобы не добавлять сложностей с разными часами на серверах и т.п.
                $exp += time();
            }
        } else {
            $exp = 0;
        }

        if ($exp == 0) {
            return true;
        }

        $mc = $this->getMcConnectionObj(); // do not check result - already checked
        if (!$mc) {
            return false;
        }

        $full_key = $this->getKey($key);

        $mcs_result = $mc->set($full_key, $value, MEMCACHE_COMPRESSED, $exp);

        if (!$mcs_result) {
            return false;
        }

        return true;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $mc = $this->getMcConnectionObj();
        if (!$mc) {
            return false;
        }

        $full_key = $this->getKey($key);
        $result = $mc->get($full_key);

        return $result;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $mc = $this->getMcConnectionObj();
        if (!$mc) {
            return false;
        }

        $full_key = $this->getKey($key);
        return $mc->delete($full_key);
    }

    /**
     * @return \Memcache|null
     * @throws \Exception
     */
    public function getMcConnectionObj()
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        $memcache_servers = $this->cache_server_settings_arr;
        if (empty($memcache_servers)) {
            return null;
        }

        // Memcached php extension not supported - slower, rare, extra features not needed
        $this->connection = new \Memcache;

        foreach ($memcache_servers as $server_settings_obj) {
            Assert::assert($this->connection->addServer($server_settings_obj->getHost(), $server_settings_obj->getPort()));
            $this->connection->setCompressThreshold(5000, 0.2);
        }

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
