<?php

namespace App\Entity;

use App\Repository\UserImportUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserImportUserRepository::class)
 */
class UserImportUser
{
    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=UserImport::class, inversedBy="userImportUsers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $userImport;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tempPassword;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isImported = false;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educatorEmail;

    /**
     * @Groups({"USER_IMPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $graduatingYear;

    /**
     * @Groups({"USER_IMPORT"})
     */
    private $errors = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserImport(): ?UserImport
    {
        return $this->userImport;
    }

    public function setUserImport(?UserImport $userImport): self
    {
        $this->userImport = $userImport;

        return $this;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getTempPassword(): ?string
    {
        return $this->tempPassword;
    }

    public function setTempPassword(?string $tempPassword): self
    {
        $this->tempPassword = $tempPassword;

        return $this;
    }

    public function getIsImported(): ?bool
    {
        if(!$this->isImported) {
            return false;
        }

        return $this->isImported;
    }

    public function setIsImported(?bool $isImported): self
    {
        $this->isImported = $isImported;

        return $this;
    }

    public function getEducatorEmail(): ?string
    {
        return $this->educatorEmail;
    }

    public function setEducatorEmail(?string $educatorEmail): self
    {
        $this->educatorEmail = $educatorEmail;

        return $this;
    }

    public function getGraduatingYear(): ?string
    {
        return $this->graduatingYear;
    }

    public function setGraduatingYear(?string $graduatingYear): self
    {
        $this->graduatingYear = $graduatingYear;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param  array  $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function fromArray($data) {

        foreach($data as $propertyName => $value) {

            switch ($propertyName) {
                case 'firstName':
                    $this->setFirstName($value);
                    break;
                case 'lastName':
                    $this->setLastName($value);
                    break;
                case 'educatorEmail':
                    $this->setEducatorEmail($value);
                    break;
                case 'graduatingYear':
                    $this->setGraduatingYear($value);
                    break;
                case 'tempPassword':
                    $this->setTempPassword($value);
                    break;
                case 'username':
                    $this->setUsername($value);
                    break;
                case 'email':
                    $this->setEmail($value);
                    break;
            }

        }
    }

}
