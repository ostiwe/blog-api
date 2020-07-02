<?php


namespace App\Controller;


use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\CacheItem;

class CacheController
{

	/** @var RedisAdapter|FilesystemAdapter $cache */
	private $cache;

	public function __construct()
	{
		if ($_ENV['USE_REDIS'] === 'true') {
			$host = $_ENV['REDIS_HOST'];
			$port = $_ENV['REDIS_PORT'];
			$dbIndex = $_ENV['REDIS_DB_INDEX'];
			$redisConnection = RedisAdapter::createConnection("redis://$host:$port/$dbIndex", [
				'compression' => true,
				'lazy' => false,
				'persistent' => 0,
				'persistent_id' => null,
				'tcp_keepalive' => 0,
				'timeout' => 30,
				'read_timeout' => 0,
				'retry_interval' => 0,
			]);
			$cache = new RedisAdapter($redisConnection, '', 0);
			$this->cache = $cache;
		} else {
			$this->cache = new FilesystemAdapter('', 0,
				dirname(__DIR__, 2) . '/var/cache/dbresp');
		}
	}

	public function inCache(string $key): bool
	{
		try {
			/** @var CacheItem $item */
			$item = $this->cache->getItem($key);
			return $item->isHit();
		} catch (InvalidArgumentException $e) {
			return false;
		}
	}

	public function getItemFromCache(string $key)
	{
		try {
			/** @var CacheItem $item */
			$item = $this->cache->getItem($key);
			if (!$item->isHit()) {
				return [];
			}
			return $item->get();
		} catch (InvalidArgumentException $e) {
			return [];
		}
	}

	public function setCache(string $key, $value): bool
	{
		$expiresAfter = (int)$_ENV['REDIS_ITEM_EXPIRES_AFTER'];
		try {
			/** @var CacheItem $newCacheItem */
			$newCacheItem = $this->cache->getItem($key);
			$newCacheItem->set($value);
			$newCacheItem->expiresAfter($expiresAfter);
			return $this->cache->save($newCacheItem);
		} catch (InvalidArgumentException $e) {
			return false;
		}
	}

	public function flushCache()
	{

		if ($_ENV['USE_REDIS'] === 'true') {
			return $this->cache->clear('*');
		}
		return $this->cache->clear();
	}
}
