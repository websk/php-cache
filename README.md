# WebSK php-cache

Optional support Memcache, Memcached, Redis

## Install

https://packagist.org/packages/websk/php-cache

install dependency using Composer

```shell
composer require websk/php-cache
```

## Configuration example

```
$config = [
    'settings' => [
        'cache' => [
            'engine' => \WebSK\Cache\Engines\Memcache::class,
            'cache_key_prefix' => 'websk',
            'servers' => [
                [
                    'host' => 'memcached',
                    'port' => 11211
                ]
            ]
        ]
    ]
];
```

## Registering a service

```
/**
 * @param ContainerInterface $container
 * @return CacheService
 */
$container['cache_service'] = function (ContainerInterface $container) {
    $cache_config = $container["settings"]["cache"];
    
    $cache_servers_arr = [];
    foreach ($cache_config['servers'] as $server_config) {
        $cache_servers_arr[] = new CacheServerSettings($server_config['host'], $server_config['port']);
    }

    /** @var CacheEngineInterface $cache_engine_class_name */
    $cache_engine_class_name = $cache_config['engine'];
    $cache_engine = new $cache_engine_class_name($cache_servers_arr, $cache_config['cache_key_prefix']);

    return new CacheService($cache_engine);
};
```