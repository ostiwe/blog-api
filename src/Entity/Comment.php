<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\Table(name="comments")
 */
class Comment
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
	 * @var User $creator
	 */
	private $creator;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $createdAt;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $deleted = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $moderated = false;

	/**
	 * @ORM\Column(type="text")
	 */
	private $text;

	/**
	 * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="commets")
	 */
	private $post;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getCreator(): ?User
	{
		return $this->creator;
	}

	public function setCreator(?User $creator): self
	{
		$this->creator = $creator;

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

	public function getDeleted(): ?bool
	{
		return $this->deleted;
	}

	public function setDeleted(bool $deleted): self
	{
		$this->deleted = $deleted;

		return $this;
	}

	public function getModerated(): ?bool
	{
		return $this->moderated;
	}

	public function setModerated(bool $moderated): self
	{
		$this->moderated = $moderated;

		return $this;
	}

	public function getText(): ?string
	{
		return $this->text;
	}

	public function setText(string $text): self
	{
		$this->text = $text;

		return $this;
	}

	public function getPost(): ?Post
	{
		return $this->post;
	}

	public function setPost(?Post $post): self
	{
		$this->post = $post;

		return $this;
	}

	public function export(): array
	{
		$creator = $this->creator;
		$creatorInfo = [
			'id' => $creator->getId(),
			'login' => $creator->getLogin(),
		];
		return [
			'id' => $this->id,
			'created_at' => $this->createdAt,
			'moderated' => $this->moderated,
			'deleted' => $this->deleted,
			'creator' => $creatorInfo,
			'text' => $this->deleted ? '### hidden' : $this->text,
		];
	}
}
