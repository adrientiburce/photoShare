<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhotoRepository")
 * @Vich\Uploadable
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
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="myPhotos")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mosaic", mappedBy="photo")
     */
    private $mosaics;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $imageName;

    /**
     * @Vich\UploadableField(mapping="photos_images", fileNameProperty="imageName")
     * @var File
     */
    private $imageFile;


    public function __construct()
    {
        $this->owner = new ArrayCollection();
        $this->mosaics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImageName($image)
    {
        $this->imageName = $image;
    }

    public function getImageName()
    {
        return $this->imageName;
    }
}
