<?php

namespace WebSK\Cache;

use WebSK\Cache\Engines\CacheEngineInterface;

class CacheService
{
    /** @var CacheEngineInterface */
    protected $cache_engine;
    /** @var array */
    protected $storage_arr = [];

    /**
     * CacheService constructor.
     * @param CacheEngineInterface $cache_engine
     */
    public function __construct(CacheEngineInterface $cache_engine)
    {
        $this->cache_engine = $cache_engine;
    }

    public function set($key, $value, $expire = -1)
    {
        $this->storage_arr[$key] = $value;
        return $this->cache_engine->set($key, $value, $expire);
    }

    public function get($key)
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

    public function delete($key)
    {
        unset($this->storage_arr[$key]);
        return $this->cache_engine->delete($key);
    }
}
