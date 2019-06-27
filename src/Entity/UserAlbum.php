<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserAlbumRepository")
 */
class UserAlbum
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userAlbums")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Album", inversedBy="userAlbums")
     */
    private $album;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEditable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOwner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function getIsEditable(): ?bool
    {
        return $this->isEditable;
    }

    public function setIsEditable(bool $isEditable): self
    {
        $this->isEditable = $isEditable;

        return $this;
    }

    public function getIsOwner(): ?bool
    {
        return $this->isOwner;
    }

    public function setIsOwner(bool $isOwner): self
    {
        $this->isOwner = $isOwner;

        return $this;
    }
}
