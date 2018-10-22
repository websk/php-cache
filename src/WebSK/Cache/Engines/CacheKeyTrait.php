<?php

namespace Websk\Cache\Engines;

/**
 * Trait CacheKeyTrait
 * @package Websk\Cache\Engines
 */
trait CacheKeyTrait
{
    /**
     * @param string $key
     * @return string
     */
    protected function getKey(string $key): string
    {
        $prefix = $this->cache_key_prefix;
        if ($prefix == '') {
            $prefix = 'default';
        }

        $full_key = sprintf('%s-%s', $prefix, $key);

        return md5($full_key);
    }
}
