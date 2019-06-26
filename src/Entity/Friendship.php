<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FriendshipRepository")
 */
class Friendship
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="friends")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="friendsWithMe")
     */
    private $friend;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getuser(): ?User
    {
        return $this->user;
    }

    public function setuser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFriend(): ?User
    {
        return $this->friend;
    }

    public function setFriend(?User $friend): self
    {
        $this->friend = $friend;

        return $this;
    }
}
