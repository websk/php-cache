<?php

namespace WebSK\Cache\Engines;

interface CacheEngineInterface
{
    public function set($key, $value, $exp);
    public function get($key);
    public function delete($key);
    public function getKey($key);
}
