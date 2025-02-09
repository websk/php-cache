<?php

namespace WebSK\Cache;

/**
 * Class CacheServerSettings
 * @package WebSK\Cache
 */
class CacheServerSettings
{
    protected string $host;
    protected int $port;

    public function __construct(string $host, int $port)
    {
        $this->setHost($host);
        $this->setPort($port);
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }
}
