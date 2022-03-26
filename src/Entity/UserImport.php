<?php

namespace App\Entity;

use App\Repository\UserImportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    private $skipColumnMappingStep = false;

    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * @ORM\OneToMany(targetEntity=UserImportUser::class, mappedBy="userImport", cascade={"persist"})
     */
    private $userImportUsers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uuid;

    public function __construct()
    {
        $this->userItems = new ArrayCollection();
        $this->userImportUsers = new ArrayCollection();
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

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): void
    {
        $this->file = $file;
    }

    public function getSkipColumnMappingStep(): bool
    {
        return $this->skipColumnMappingStep;
    }

    public function setSkipColumnMappingStep(bool $skipColumnMappingStep): void
    {
        $this->skipColumnMappingStep = $skipColumnMappingStep;
    }


/*    public function setUserImportUsers($userImportUsers) {
        $this->userImportUsers = $userImportUsers;
    }

    public function getUserImportUsers(): array
    {
        return $this->userImportUsers;
    }*/

    /**
     * @return Collection|UserImportUser[]
     */
    public function getUserImportUsers(): Collection
    {
        return $this->userImportUsers;
    }

    public function addUserImportUser(UserImportUser $userImportUser): self
    {
        if (!$this->userImportUsers->contains($userImportUser)) {
            $this->userImportUsers[] = $userImportUser;
            $userImportUser->setUserImport($this);
        }

        return $this;
    }

    public function removeUserImportUser(UserImportUser $userImportUser): self
    {
        if ($this->userImportUsers->removeElement($userImportUser)) {
            // set the owning side to null (unless already changed)
            if ($userImportUser->getUserImport() === $this) {
                $userImportUser->setUserImport(null);
            }
        }

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
