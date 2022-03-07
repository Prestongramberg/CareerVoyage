<?php

namespace App\Entity;

use App\Repository\UserImportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Arr;

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
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity=School::class, inversedBy="userImports")
     */
    private $school;

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
    private $graduatingYearMapping;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educatorEmailMapping;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailMapping;

    private $users = [];

    private $userItems;

    public function __construct()
    {
        $this->userItems = new ArrayCollection();
    }
    
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

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

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

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers($users) {
        $this->users = $users;
    }

    public function addUser(User $user): self
    {
        $this->users[] = $user;

        return $this;
    }

    public function removeUser(User $user): self
    {
        // todo?

        return $this;
    }


    public function getUserItems(): ArrayCollection
    {
        return $this->userItems;
    }

    public function addUserItem(User $user): self
    {
        $this->userItems[] = $user;

        return $this;
    }

    public function setUserItems($userItems): self
    {
        $this->userItems = $userItems;

        return $this;
    }

    public function removeUserItem(User $user): self
    {
        // todo?

        return $this;
    }

    public function getGraduatingYearMapping(): ?string
    {
        return $this->graduatingYearMapping;
    }

    public function setGraduatingYearMapping(?string $graduatingYearMapping): self
    {
        $this->graduatingYearMapping = $graduatingYearMapping;

        return $this;
    }

    public function getEducatorEmailMapping(): ?string
    {
        return $this->educatorEmailMapping;
    }

    public function setEducatorEmailMapping(?string $educatorEmailMapping): self
    {
        $this->educatorEmailMapping = $educatorEmailMapping;

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
}
