<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
	 * @ORM\JoinColumn(nullable=false)
	 * @var User $creator
	 */
	private $creator;

	/**
	 * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="posts")
	 */
	private $tags;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $title;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $description;

	/**
	 * @ORM\Column(type="text")
	 */
	private $content;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $views = 0;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $published;

	public function __construct()
	{
		$this->tags = new ArrayCollection();
	}

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

	/**
	 * @return Collection|Tag[]
	 */
	public function getTags(): Collection
	{
		return $this->tags;
	}

	public function addTag(Tag $tag): self
	{
		if (!$this->tags->contains($tag)) {
			$this->tags[] = $tag;
		}

		return $this;
	}

	public function removeTag(Tag $tag): self
	{
		if ($this->tags->contains($tag)) {
			$this->tags->removeElement($tag);
		}

		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getContent(): ?string
	{
		return $this->content;
	}

	public function setContent(string $content): self
	{
		$this->content = $content;

		return $this;
	}

	public function getViews(): ?int
	{
		return $this->views;
	}

	public function setViews(int $views): self
	{
		$this->views = $views;

		return $this;
	}

	public function getPublished(): ?int
	{
		return $this->published;
	}

	public function setPublished(int $published): self
	{
		$this->published = $published;

		return $this;
	}

	public function export(): array
	{
		$tags = $this->tags->map(function ($tag) { return $tag->export(); })->toArray();
		return [
			'id' => $this->id,
			'creator' => [
				'login' => $this->creator->getLogin(),
				'mask' => $this->creator->getMask(),
			],
			'published' => $this->published,
			'title' => $this->title,
			'description' => $this->description,
			'content' => $this->content,
			'tags' => $tags,
		];
	}


}
