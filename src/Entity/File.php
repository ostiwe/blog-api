<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;


	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="files")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $owner;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $path;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $size;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $uploaded;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $fileType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

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

	public function getPath(): ?string
         	{
         		return $this->path;
         	}

	public function setPath(string $path): self
         	{
         		$this->path = $path;

         		return $this;
         	}

	public function getSize(): ?string
         	{
         		return $this->size;
         	}

	public function setSize(string $size): self
         	{
         		$this->size = $size;

         		return $this;
         	}

	public function getUploaded(): ?int
         	{
         		return $this->uploaded;
         	}

	public function setUploaded(int $uploaded): self
         	{
         		$this->uploaded = $uploaded;

         		return $this;
         	}

	public function getFileType(): ?string
         	{
         		return $this->fileType;
         	}

	public function setFileType(string $fileType): self
         	{
         		$this->fileType = $fileType;

         		return $this;
         	}

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
