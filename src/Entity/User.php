<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\Length(min=4, minMessage="Votre mot de passe doit contenir au moins 4 caractères")
     * @Assert\EqualTo(propertyPath="confirm_password", message="Vos mot de passe sont différents")
     */
    private $password;

    /**
     * @var string
     * @Assert\EqualTo(propertyPath="password", message="Vos mot de passe sont différents")
     */
    private $confirmPassword;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Friendship", mappedBy="user")
     */
    private $friends;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Friendship", mappedBy="friend")
     */
    private $friendsWithMe;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAlbum", mappedBy="user")
     */
    private $userAlbums;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Photo", mappedBy="owner")
     */
    private $myPhotos;


    public function __construct()
    {
        $this->albums = new ArrayCollection();
        $this->friends = new ArrayCollection();
        $this->friendsWithMe = new ArrayCollection();
        $this->userAlbums = new ArrayCollection();
        $this->myPhotos = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getConfirmPassword()
    {
        return $this->confirmPassword;
    }

    /**
     * @param string $confirmPassword
     */
    public function setConfirmPassword(string $confirmPassword)
    {
        $this->confirmPassword = $confirmPassword;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|Friendship[]
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(Friendship $friend): self
    {
        if (!$this->friends->contains($friend)) {
            $this->friends[] = $friend;
            $friend->setFriendsWithMe($this);
        }

        return $this;
    }

    public function removeFriend(Friendship $friend): self
    {
        if ($this->friends->contains($friend)) {
            $this->friends->removeElement($friend);
            // set the owning side to null (unless already changed)
            if ($friend->getFriendsWithMe() === $this) {
                $friend->setFriendsWithMe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friendship[]
     */
    public function getFriendsWithMe(): Collection
    {
        return $this->friendsWithMe;
    }

    public function addFriendsWithMe(Friendship $friendsWithMe): self
    {
        if (!$this->friendsWithMe->contains($friendsWithMe)) {
            $this->friendsWithMe[] = $friendsWithMe;
            $friendsWithMe->setFriend($this);
        }

        return $this;
    }

    public function removeFriendsWithMe(Friendship $friendsWithMe): self
    {
        if ($this->friendsWithMe->contains($friendsWithMe)) {
            $this->friendsWithMe->removeElement($friendsWithMe);
            // set the owning side to null (unless already changed)
            if ($friendsWithMe->getFriend() === $this) {
                $friendsWithMe->setFriend(null);
            }
        }

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
            $userAlbum->setUser($this);
        }

        return $this;
    }

    public function removeUserAlbum(UserAlbum $userAlbum): self
    {
        if ($this->userAlbums->contains($userAlbum)) {
            $this->userAlbums->removeElement($userAlbum);
            // set the owning side to null (unless already changed)
            if ($userAlbum->getUser() === $this) {
                $userAlbum->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Photo[]
     */
    public function getMyPhotos(): Collection
    {
        return $this->myPhotos;
    }

    public function addMyPhoto(Photo $myPhoto): self
    {
        if (!$this->myPhotos->contains($myPhoto)) {
            $this->myPhotos[] = $myPhoto;
            $myPhoto->addOwner($this);
        }

        return $this;
    }

    public function removeMyPhoto(Photo $myPhoto): self
    {
        if ($this->myPhotos->contains($myPhoto)) {
            $this->myPhotos->removeElement($myPhoto);
            $myPhoto->removeOwner($this);
        }

        return $this;
    }


}
