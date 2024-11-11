<?php

namespace App\Service;

interface CacheInterface
{
    public function get(string $key);
    public function set(string $key, $value, int $ttl = 600): void;
    public function delete(string $key): void;
}