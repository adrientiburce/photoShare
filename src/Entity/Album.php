<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AlbumRepository")
 */
class Album
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAlbum", mappedBy="album")
     */
    private $userAlbums;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mosaic", mappedBy="album", orphanRemoval=true)
     */
    private $mosaics;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Photo", inversedBy="albums")
     */
    private $photos;

    public function __construct()
    {
        $this->owner = new ArrayCollection();
        $this->userAlbums = new ArrayCollection();
        $this->mosaics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|UserAlbum[]
     */
    public function getUserAlbums(): Collection
    {
        return $this->userAlbums;
    }

    public function addUserAlbum(UserAlbum $userAlbum): self
    {
        if (!$this->userAlbums->contains($userAlbum)) {
            $this->userAlbums[] = $userAlbum;
            $userAlbum->setAlbum($this);
        }

        return $this;
    }

    public function removeUserAlbum(UserAlbum $userAlbum): self
    {
        if ($this->userAlbums->contains($userAlbum)) {
            $this->userAlbums->removeElement($userAlbum);
            // set the owning side to null (unless already changed)
            if ($userAlbum->getAlbum() === $this) {
                $userAlbum->setAlbum(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Mosaic[]
     */
    public function getMosaics(): Collection
    {
        return $this->mosaics;
    }

    public function addMosaic(Mosaic $mosaic): self
    {
        if (!$this->mosaics->contains($mosaic)) {
            $this->mosaics[] = $mosaic;
            $mosaic->setAlbum($this);
        }

        return $this;
    }

    public function removeMosaic(Mosaic $mosaic): self
    {
        if ($this->mosaics->contains($mosaic)) {
            $this->mosaics->removeElement($mosaic);
            // set the owning side to null (unless already changed)
            if ($mosaic->getAlbum() === $this) {
                $mosaic->setAlbum(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Photo[]
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->contains($photo)) {
            $this->photos->removeElement($photo);
        }

        return $this;
    }
}
