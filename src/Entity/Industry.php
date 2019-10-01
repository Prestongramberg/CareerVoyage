<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IndustryRepository")
 */
class Industry
{
    /**
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "EXPERIENCE_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Company", mappedBy="primaryIndustry")
     */
    private $companies;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\OneToMany(targetEntity="App\Entity\SecondaryIndustry", mappedBy="primaryIndustry")
     */
    private $secondaryIndustries;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lesson", mappedBy="primaryIndustry")
     */
    private $lessons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProfessionalUser", mappedBy="primaryIndustry")
     */
    private $professionalUsers;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->secondaryIndustries = new ArrayCollection();
        $this->lessons = new ArrayCollection();
        $this->professionalUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->setPrimaryIndustry($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            // set the owning side to null (unless already changed)
            if ($company->getPrimaryIndustry() === $this) {
                $company->setPrimaryIndustry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SecondaryIndustry[]
     */
    public function getSecondaryIndustries(): Collection
    {
        return $this->secondaryIndustries;
    }

    public function addSecondaryIndustry(SecondaryIndustry $secondaryIndustry): self
    {
        if (!$this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries[] = $secondaryIndustry;
            $secondaryIndustry->setPrimaryIndustry($this);
        }

        return $this;
    }

    public function removeSecondaryIndustry(SecondaryIndustry $secondaryIndustry): self
    {
        if ($this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries->removeElement($secondaryIndustry);
            // set the owning side to null (unless already changed)
            if ($secondaryIndustry->getPrimaryIndustry() === $this) {
                $secondaryIndustry->setPrimaryIndustry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Lesson[]
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): self
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons[] = $lesson;
            $lesson->setPrimaryIndustry($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): self
    {
        if ($this->lessons->contains($lesson)) {
            $this->lessons->removeElement($lesson);
            // set the owning side to null (unless already changed)
            if ($lesson->getPrimaryIndustry() === $this) {
                $lesson->setPrimaryIndustry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProfessionalUser[]
     */
    public function getProfessionalUsers(): Collection
    {
        return $this->professionalUsers;
    }

    public function addProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if (!$this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers[] = $professionalUser;
            $professionalUser->setPrimaryIndustry($this);
        }

        return $this;
    }

    public function removeProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if ($this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers->removeElement($professionalUser);
            // set the owning side to null (unless already changed)
            if ($professionalUser->getPrimaryIndustry() === $this) {
                $professionalUser->setPrimaryIndustry(null);
            }
        }

        return $this;
    }
}
