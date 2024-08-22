<?php

namespace WebSK\Cache\Engines;

use WebSK\Utils\Assert;
use Predis\Client;
use WebSK\Cache\CacheServerSettings;

/**
 * Class Redis
 * @package WebSK\Cache\Engines
 */
class Redis implements CacheEngineInterface
{
    use CacheKeyTrait;

    const string CONNECTION_SCHEME_TCP = 'tcp';

    /** @var CacheServerSettings[] */
    protected array $cache_server_settings_arr = [];
    protected Client $connection;
    protected array $params_arr = [];
    protected string $cache_key_prefix = '';

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


    /** @inheritdoc */
    public function set(string $key, $value, int $ttl_sec = 0): bool
    {
        Assert::assert($ttl_sec >= 0);

        $connection_obj = $this->getConnectionObj();
        if (!$connection_obj) {
            return false;
        }

        $full_key = $this->getKey($key);
        $value_ser = serialize($value);

        if ($ttl_sec > 0) {
            $result = $connection_obj->setex($full_key, $ttl_sec, $value_ser);
        } else {
            $result = $connection_obj->set($full_key, $value_ser);
        }

        if (!$result) {
            return false;
        }

        return true;
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

        if ($result === false) {
            return false;
        }

        $result = unserialize($result);

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
        $result = $connection_obj->del([$full_key]);

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * @return null|Client
     */
    protected function getConnectionObj(): ?Client
    {
        if ($this->connection instanceof Client) {
            return $this->connection;
        }

        if (empty($this->cache_server_settings_arr)) {
            return null;
        }

        $servers_arr = [];
        foreach ($this->cache_server_settings_arr as $server_settings_obj) {
            $servers_arr[] = [
                'scheme' => self::CONNECTION_SCHEME_TCP,
                'host' => $server_settings_obj->getHost(),
                'port' => $server_settings_obj->getPort()
            ];
        }

        $this->connection = new Client($servers_arr, $this->params_arr);

        return $this->connection;
    }
}
