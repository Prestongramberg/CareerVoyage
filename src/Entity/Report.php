<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportRepository::class)
 */
class Report
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $companyNames = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $companies = [];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $registrationDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $school;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolName;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $schoolNames = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $schools = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $professional;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $professionalName;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $professionals = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $professionalNames = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experience;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceTypeId;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $experiences = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $experienceNames = [];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $experienceStartDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $regionName;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $regions = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $regionNames = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $student;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $studentName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educatorName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyNames(): ?array
    {
        return $this->companyNames;
    }

    public function setCompanyNames(?array $companyNames): self
    {
        $this->companyNames = array_values(array_unique($companyNames));

        return $this;
    }

    public function getCompanies(): ?array
    {
        return $this->companies;
    }

    public function setCompanies(?array $companies): self
    {
        $this->companies = array_values(array_unique($companies));

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(?\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(?string $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getSchoolName(): ?string
    {
        return $this->schoolName;
    }

    public function setSchoolName(?string $schoolName): self
    {
        $this->schoolName = $schoolName;

        return $this;
    }

    public function getSchools(): ?array
    {
        return $this->schools;
    }

    public function setSchools(?array $schools): self
    {
        $this->schools = array_values(array_unique($schools));

        return $this;
    }

    public function getSchoolNames(): ?array
    {
        return $this->schoolNames;
    }

    public function setSchoolNames(?array $schoolNames): self
    {
        $this->schoolNames = array_values(array_unique($schoolNames));

        return $this;
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

    public function getProfessional(): ?string
    {
        return $this->professional;
    }

    public function setProfessional(?string $professional): self
    {
        $this->professional = $professional;

        return $this;
    }

    public function getProfessionalName(): ?string
    {
        return $this->professionalName;
    }

    public function setProfessionalName(?string $professionalName): self
    {
        $this->professionalName = $professionalName;

        return $this;
    }

    public function getProfessionals(): ?array
    {
        return $this->professionals;
    }

    public function setProfessionals(?array $professionals): self
    {
        $this->professionals = array_values(array_unique($professionals));

        return $this;
    }

    public function getProfessionalNames(): ?array
    {
        return $this->professionalNames;
    }

    public function setProfessionalNames(?array $professionalNames): self
    {
        $this->professionalNames = array_values(array_unique($professionalNames));

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getExperienceName(): ?string
    {
        return $this->experienceName;
    }

    public function setExperienceName(?string $experienceName): self
    {
        $this->experienceName = $experienceName;

        return $this;
    }

    public function getExperienceType(): ?string
    {
        return $this->experienceType;
    }

    public function setExperienceType(?string $experienceType): self
    {
        $this->experienceType = $experienceType;

        return $this;
    }

    public function getExperienceTypeId(): ?string
    {
        return $this->experienceTypeId;
    }

    public function setExperienceTypeId(?string $experienceTypeId): self
    {
        $this->experienceTypeId = $experienceTypeId;

        return $this;
    }

    public function getExperiences(): ?array
    {
        return $this->experiences;
    }

    public function setExperiences(?array $experiences): self
    {
        $this->experiences = array_values(array_unique($experiences));

        return $this;
    }

    public function getExperienceNames(): ?array
    {
        return $this->experienceNames;
    }

    public function setExperienceNames(?array $experienceNames): self
    {
        $this->experienceNames = array_values(array_unique($experienceNames));

        return $this;
    }

    public function getExperienceStartDate(): ?\DateTimeInterface
    {
        return $this->experienceStartDate;
    }

    public function setExperienceStartDate(?\DateTimeInterface $experienceStartDate): self
    {
        $this->experienceStartDate = $experienceStartDate;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    public function setRegionName(?string $regionName): self
    {
        $this->regionName = $regionName;

        return $this;
    }

    public function getRegions(): ?array
    {
        return $this->regions;
    }

    public function setRegions(?array $regions): self
    {
        $this->regions = array_values(array_unique($regions));

        return $this;
    }

    public function getRegionNames(): ?array
    {
        return $this->regionNames;
    }

    public function setRegionNames(?array $regionNames): self
    {
        $this->regionNames = array_values(array_unique($regionNames));

        return $this;
    }

    public function getStudent(): ?string
    {
        return $this->student;
    }

    public function setStudent(?string $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getStudentName(): ?string
    {
        return $this->studentName;
    }

    public function setStudentName(?string $studentName): self
    {
        $this->studentName = $studentName;

        return $this;
    }

    public function getEducator(): ?string
    {
        return $this->educator;
    }

    public function setEducator(?string $educator): self
    {
        $this->educator = $educator;

        return $this;
    }

    public function getEducatorName(): ?string
    {
        return $this->educatorName;
    }

    public function setEducatorName(?string $educatorName): self
    {
        $this->educatorName = $educatorName;

        return $this;
    }
}
