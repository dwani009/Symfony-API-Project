<?php

namespace App\Service;

use Predis\Client as RedisClient;

class RedisCacheService implements CacheInterface
{
    private RedisClient $redisClient;

    /**
     * @param RedisClient $redisClient
     */
    public function __construct(RedisClient $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        return $this->redisClient->get($key);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return void
     */
    public function set(string $key, $value, int $ttl = 600): void
    {
        $this->redisClient->setex($key, $ttl, json_encode($value));
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        $this->redisClient->del($key);
    }
}