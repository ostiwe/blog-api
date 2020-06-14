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

	const USER_BLOCKED = 1 << 0;


	const USER_DEFAULT_MASK = self::CAN_READ;

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

	public function __construct()
               	{
               		$this->posts = new ArrayCollection();
               		$this->accessTokens = new ArrayCollection();
                 $this->comments = new ArrayCollection();
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
               			'first_name' => $this->firstName,
               			'last_name' => $this->lastName,
               			'mask' => $this->mask,
               			'email' => $this->email,
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
}
