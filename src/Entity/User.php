<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{

	/* Битовые маски доступные пользователю */
	const CAN_READ = 1 << 1;
	const CAN_CREATE_POST = 1 << 2;
	const CAN_UPLOAD_FILES = 1 << 3;
	const CAN_CREATE_COMMENT = 1 << 4;
	const CAN_DELETE_COMMENT = 1 << 5;
	const CAN_WRITE_MESSAGES = 1 << 6;

	const COMMENTS_NO_NEED_MODERATE = 1 << 7;

	const USER_BLOCKED = 1 << 0;


	const USER_DEFAULT_MASK = self::CAN_READ;

	const FULL_ADMIN =
		self::CAN_READ |
		self::CAN_CREATE_POST |
		self::CAN_UPLOAD_FILES |
		self::CAN_CREATE_COMMENT |
		self::CAN_DELETE_COMMENT |
		self::CAN_WRITE_MESSAGES |
		self::COMMENTS_NO_NEED_MODERATE;

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $login;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $firstName;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $lastName;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $sex;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $password;

	/**
	 * @ORM\OneToMany(targetEntity=Post::class, mappedBy="creator")
	 */
	private $posts;

	/**
	 * @ORM\OneToMany(targetEntity=AccessToken::class, mappedBy="owner", orphanRemoval=true)
	 */
	private $accessTokens;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $mask;

	/**
	 * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="creator")
	 */
	private $comments;

	/**
	 * @ORM\ManyToOne(targetEntity=Lang::class)
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $lang;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $avatar = "193.jpg";

	/**
	 * @ORM\OneToMany(targetEntity=File::class, mappedBy="owner")
	 */
	private $files;

	public function __construct()
	{
		$this->posts = new ArrayCollection();
		$this->accessTokens = new ArrayCollection();
		$this->comments = new ArrayCollection();
		$this->files = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getLogin(): ?string
	{
		return $this->login;
	}

	public function setLogin(string $login): self
	{
		$this->login = $login;

		return $this;
	}

	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	public function setFirstName(?string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	public function setLastName(?string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}

	public function getSex(): ?int
	{
		return $this->sex;
	}

	public function setSex(?int $sex): self
	{
		$this->sex = $sex;

		return $this;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPassword(string $password): self
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * @return Collection|Post[]
	 */
	public function getPosts(): Collection
	{
		return $this->posts;
	}

	public function addPost(Post $post): self
	{
		if (!$this->posts->contains($post)) {
			$this->posts[] = $post;
			$post->setCreator($this);
		}

		return $this;
	}

	public function removePost(Post $post): self
	{
		if ($this->posts->contains($post)) {
			$this->posts->removeElement($post);
			// set the owning side to null (unless already changed)
			if ($post->getCreator() === $this) {
				$post->setCreator(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|AccessToken[]
	 */
	public function getAccessTokens(): Collection
	{
		return $this->accessTokens;
	}

	public function addAccessToken(AccessToken $accessToken): self
	{
		if (!$this->accessTokens->contains($accessToken)) {
			$this->accessTokens[] = $accessToken;
			$accessToken->setOwner($this);
		}

		return $this;
	}

	public function removeAccessToken(AccessToken $accessToken): self
	{
		if ($this->accessTokens->contains($accessToken)) {
			$this->accessTokens->removeElement($accessToken);
			// set the owning side to null (unless already changed)
			if ($accessToken->getOwner() === $this) {
				$accessToken->setOwner(null);
			}
		}

		return $this;
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

	public function export()
	{
		return [
			'id' => $this->id,
			'login' => $this->login,
			'avatar' => $this->avatar,
			'first_name' => $this->firstName,
			'last_name' => $this->lastName,
			'mask' => $this->mask,
			'email' => $this->email,
			'locale' => $this->getLang()->getCode(),
		];
	}

	/**
	 * @return Collection|Comment[]
	 */
	public function getComments(): Collection
	{
		return $this->comments;
	}

	public function addComment(Comment $comment): self
	{
		if (!$this->comments->contains($comment)) {
			$this->comments[] = $comment;
			$comment->setCreator($this);
		}

		return $this;
	}

	public function removeComment(Comment $comment): self
	{
		if ($this->comments->contains($comment)) {
			$this->comments->removeElement($comment);
			// set the owning side to null (unless already changed)
			if ($comment->getCreator() === $this) {
				$comment->setCreator(null);
			}
		}

		return $this;
	}

	public function getLang(): ?Lang
	{
		return $this->lang;
	}

	public function setLang(?Lang $lang): self
	{
		$this->lang = $lang;

		return $this;
	}

	public function getAvatar(): ?string
	{
		return $this->avatar;
	}

	public function setAvatar(?string $avatar): self
	{
		$this->avatar = $avatar;

		return $this;
	}

	/**
	 * @return Collection|File[]
	 */
	public function getFiles(): Collection
	{
		return $this->files;
	}

	public function addFile(File $file): self
	{
		if (!$this->files->contains($file)) {
			$this->files[] = $file;
			$file->setOwner($this);
		}

		return $this;
	}

	public function removeFile(File $file): self
	{
		if ($this->files->contains($file)) {
			$this->files->removeElement($file);
			// set the owning side to null (unless already changed)
			if ($file->getOwner() === $this) {
				$file->setOwner(null);
			}
		}

		return $this;
	}
}
