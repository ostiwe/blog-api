<?php

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity(repositoryClass=AccessTokenRepository::class)
 */
class AccessToken
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="accessTokens")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $owner;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $value;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $mask;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $createdAt;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $expiredAt;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getOwner(): ?User
	{
		return $this->owner;
	}

	public function setOwner(?User $owner): self
	{
		$this->owner = $owner;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function getMask(): ?int
	{
		return $this->mask;
	}

	public function setMask(int $mask): self
	{
		$this->mask = $mask;

		return $this;
	}

	public function getCreatedAt(): ?int
	{
		return $this->createdAt;
	}

	public function setCreatedAt(int $createdAt): self
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getExpiredAt(): ?int
	{
		return $this->expiredAt;
	}

	public function setExpiredAt(int $expiredAt): self
	{
		$this->expiredAt = $expiredAt;

		return $this;
	}

	public function generate(): self
	{
		try {
			$token = bin2hex(random_bytes(20));
		} catch (Exception $e) {
			$token = bin2hex(rand(PHP_INT_MIN, PHP_INT_MAX) . "{$this->getCreatedAt()}|{$this->getExpiredAt()}" . rand(PHP_INT_MIN, PHP_INT_MAX));
		}
		$this->value = $token;
		return $this;
	}

	public function export(): array
	{
		return [
			'id' => $this->id,
			'value' => $this->value,
			'mask' => $this->mask,
			'created_at' => $this->createdAt,
			'expired_at' => $this->expiredAt,
		];
	}
}
