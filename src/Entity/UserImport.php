<?php

namespace App\Entity;

use App\Repository\UserImportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserImportRepository::class)
 */
class UserImport
{
    public const TYPE_STUDENT  = 'TYPE_STUDENT';
    public const TYPE_EDUCATOR = 'TYPE_EDUCATOR';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $autogenerateUsername = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $autogeneratePassword = true;

    /**
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity=EducatorUser::class, inversedBy="userImports")
     */
    private $educator;

    /**
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity=School::class, inversedBy="userImports")
     */
    private $school;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstNameMapping;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastNameMapping;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailMapping;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $usernameMapping;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAutogenerateUsername(): ?bool
    {
        return $this->autogenerateUsername;
    }

    public function setAutogenerateUsername(?bool $autogenerateUsername): self
    {
        $this->autogenerateUsername = $autogenerateUsername;

        return $this;
    }

    public function getAutogeneratePassword(): ?bool
    {
        return $this->autogeneratePassword;
    }

    public function setAutogeneratePassword(?bool $autogeneratePassword): self
    {
        $this->autogeneratePassword = $autogeneratePassword;

        return $this;
    }

    public function getEducator(): ?EducatorUser
    {
        return $this->educator;
    }

    public function setEducator(?EducatorUser $educator): self
    {
        $this->educator = $educator;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFirstNameMapping(): ?string
    {
        return $this->firstNameMapping;
    }

    public function setFirstNameMapping(?string $firstNameMapping): self
    {
        $this->firstNameMapping = $firstNameMapping;

        return $this;
    }

    public function getLastNameMapping(): ?string
    {
        return $this->lastNameMapping;
    }

    public function setLastNameMapping(?string $lastNameMapping): self
    {
        $this->lastNameMapping = $lastNameMapping;

        return $this;
    }

    public function getEmailMapping(): ?string
    {
        return $this->emailMapping;
    }

    public function setEmailMapping(?string $emailMapping): self
    {
        $this->emailMapping = $emailMapping;

        return $this;
    }

    public function getUsernameMapping(): ?string
    {
        return $this->usernameMapping;
    }

    public function setUsernameMapping(?string $usernameMapping): self
    {
        $this->usernameMapping = $usernameMapping;

        return $this;
    }
}
