<?php


namespace App\Services;


class RequestStorage
{
	private array $storage = [];

	/**
	 * @return array
	 */
	public function getStorage(): array
	{
		return $this->storage;
	}

	/**
	 * @param array $storage
	 */
	public function setStorage(array $storage): void
	{
		$this->storage = $storage;
	}

	/**
	 * @param string $key
	 *
	 * @return bool|mixed
	 */
	public function get(string $key)
	{
		if (key_exists($key, $this->storage)) return $this->storage[$key];
		return false;
	}

	/**
	 * @param string $key
	 * @param        $value
	 */
	public function set(string $key, $value)
	{
		$this->storage[$key] = $value;
	}

}