<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

/**
 * Class ResetPassword
 * @package App\Model
 */
class ResetPassword
{
    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, minLength = "8")
     * @Assert\NotBlank(message="Please enter a password")
     *
     * @var string The hashed password
     */
    private $password;

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }
}