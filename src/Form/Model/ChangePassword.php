<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ChangePassword
{

    /**
     * @SecurityAssert\UserPassword(
     *     message = "Votre mot de passe actuelle est erroné",
     * )
     */
    private $oldPassword;

    /**
     * @Assert\Length(min=4, minMessage="Votre mot de passe doit contenir au moins 4 caractères")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Mot de passe renseigné différent du mot de passe défini")
     */
    public $confirmPassword;

    /**
     * Get message = "Wrong value for your current password"
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * Set message = "Wrong value for your current password"
     *
     * @return  self
     */
    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    /**
     * Get the value of confirmPassword
     */
    public function getConfirmPassword()
    {
        return $this->confirmPassword;
    }

    /**
     * Set the value of confirmPassword
     *
     * @return  self
     */
    public function setConfirmPassword($confirmPassword)
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}
