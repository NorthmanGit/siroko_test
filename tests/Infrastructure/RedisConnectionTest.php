<?php

namespace App\Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisConnectionTest extends TestCase
{
    public function testRedisConnection(): void
    {
        $redis = new Client('redis://redis:6379');
        $redis->set('ping', 'pong');
        $this->assertSame('pong', $redis->get('ping'));
    }
}
