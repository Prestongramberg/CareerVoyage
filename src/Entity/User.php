<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     * @Assert\NotBlank(message="Don't forget an email for your user!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6", groups={"CREATE", "EDIT"})
     * @Assert\NotBlank(message="Don't forget a password for your user!", groups={"CREATE"})
     *
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(message="Don't forget the password repeat field!", groups={"CREATE"})
     * @var string password repeat
     */
    private $passwordRepeat;

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     * @Assert\NotBlank(message="Don't forget a first name for your user!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=24)
     */
    private $firstName;

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     * @Assert\NotBlank(message="Don't forget a last name for your user!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=24)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $passwordResetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordResetTokenTimestamp;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
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
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
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

    /**
     * @return string
     */
    public function getPasswordRepeat(): ?string
    {
        return $this->passwordRepeat;
    }

    /**
     * @param string $passwordRepeat
     */
    public function setPasswordRepeat(?string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    /**
     * @param string $passwordResetToken
     * @return User
     * @throws \Exception
     */
    public function setPasswordResetToken($passwordResetToken = null)
    {
        if (empty($passwordResetToken)) {
            $passwordResetToken = bin2hex(random_bytes(32));
        }

        if (strlen($passwordResetToken) !== 64) {
            throw new \InvalidArgumentException('Reset token must be 64 characters in length');
        }

        $this->passwordResetToken = $passwordResetToken;

        $this->setPasswordResetTokenTimestamp();

        return $this;
    }

    public function getPasswordResetTokenTimestamp(): ?\DateTimeInterface
    {
        return $this->passwordResetTokenTimestamp;
    }

    /**
     * @param DateTime $passwordResetTokenTimestamp
     * @return User
     * @throws \Exception
     */
    public function setPasswordResetTokenTimestamp(DateTime $passwordResetTokenTimestamp = null)
    {
        if (empty($passwordResetTokenTimestamp)) {
            $passwordResetTokenTimestamp = new DateTime();
        }

        $this->passwordResetTokenTimestamp = $passwordResetTokenTimestamp;

        return $this;
    }

    /**
     * Clear out password reset token related fields
     *
     * @return User
     */
    public function clearPasswordResetToken()
    {
        $this->passwordResetToken          = null;
        $this->passwordResetTokenTimestamp = null;

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }
}
