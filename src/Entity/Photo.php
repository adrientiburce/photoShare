<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhotoRepository")
 */
class Photo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="myPhotos")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mosaic", mappedBy="photo")
     */
    private $mosaics;

    public function __construct()
    {
        $this->owner = new ArrayCollection();
        $this->mosaics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getOwner(): Collection
    {
        return $this->owner;
    }

    public function addOwner(User $owner): self
    {
        if (!$this->owner->contains($owner)) {
            $this->owner[] = $owner;
        }

        return $this;
    }

    public function removeOwner(User $owner): self
    {
        if ($this->owner->contains($owner)) {
            $this->owner->removeElement($owner);
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
            $mosaic->setPhoto($this);
        }

        return $this;
    }

    public function removeMosaic(Mosaic $mosaic): self
    {
        if ($this->mosaics->contains($mosaic)) {
            $this->mosaics->removeElement($mosaic);
            // set the owning side to null (unless already changed)
            if ($mosaic->getPhoto() === $this) {
                $mosaic->setPhoto(null);
            }
        }

        return $this;
    }
}
