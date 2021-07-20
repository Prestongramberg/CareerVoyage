<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ReportRepository::class)
 */
class Report
{
    const TYPE_DASHBOARD = 'TYPE_DASHBOARD';
    const TYPE_BUILDER   = 'TYPE_BUILDER';

    public static $reportEntityClassNameMap = [
        'Admin User' => AdminUser::class,
        'Chat' => Chat::class,
        'Chat message' => ChatMessage::class,
        'Company' => Company::class,
        'Company Experience' => CompanyExperience::class,
        'Company Favorites' => CompanyFavorite::class,
        'Course' => Course::class,
        'Educator User' => EducatorUser::class,
        'Experience' => Experience::class,
        'Experience Shares' => Share::class,
        'Feedback' => Feedback::class,
        'Grade' => Grade::class,
        'Primary Industries' => Industry::class,
        'Lesson' => Lesson::class,
        'Lesson Favorites' => LessonFavorite::class,
        'Lessons I can teach' => ReportLessonsCanTeach::class,
        'Lessons I want taught' => ReportLessonsWantTaught::class,
        'Professional User' => ProfessionalUser::class,
        'Region' => Region::class,
        'Regional Coordinator User' => RegionalCoordinator::class,
        'Registration' => Registration::class,
        'Roles willing to fulfill / Experience types' => RolesWillingToFulfill::class,
        'School' => School::class,
        'School Experience' => SchoolExperience::class,
        'School Administrator User' => SchoolAdministrator::class,
        'Secondary Industry' => SecondaryIndustry::class,
        'Site Admin User' => SiteAdminUser::class,
        'State' => State::class,
        'Student To Meet Professional Experience (One on One / Informational Interview)' => StudentToMeetProfessionalExperience::class,
        'Student User' => StudentUser::class,
        'Teach Lesson Experience (Topic Instructor)' => TeachLessonExperience::class,
        'User' => User::class,
    ];

    public static function getUserRoles(): array
    {
        return [
            User::ROLE_PROFESSIONAL_USER => 'Professional User',
            User::ROLE_EDUCATOR_USER => 'Educator User',
            User::ROLE_STUDENT_USER => 'Student User',
            User::ROLE_ADMIN_USER => 'Super Administrator User',
            User::ROLE_STATE_COORDINATOR_USER => 'State Coordinator User',
            User::ROLE_REGIONAL_COORDINATOR_USER => 'Regional Coordinator User',
            User::ROLE_SCHOOL_ADMINISTRATOR_USER => 'School Administrator User',
            User::ROLE_SITE_ADMIN_USER => 'Site Administrator User',
        ];
    }

    /**
     * @Groups({"REPORT"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $companyNames = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $companies = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="date", nullable=true)
     */
    private $registrationDate;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $school;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $schoolNames = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $schools = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $professional;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $professionalName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $professionals = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $professionalNames = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experience;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceType;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceTypeId;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $experiences = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $experienceNames = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="date", nullable=true)
     */
    private $experienceStartDate;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $regionName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $regions = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $regionNames = [];

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $student;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $studentName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educator;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educatorName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dashboardType;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $participationType;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registration;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceClass;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $studentFirstName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $studentLastName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $professionalFirstName;

    /**
     * @Groups({"REPORT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $professionalLastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reportType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reportEntityClassName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reportName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $reportDescription;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $reportRules;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReportColumn", mappedBy="report", orphanRemoval=true, cascade={"remove", "persist"})
     */
    private $reportColumns;

    /**
     * @ORM\OneToOne(targetEntity=ReportShare::class, mappedBy="report", cascade={"persist", "remove"})
     */
    private $reportShare;

    /**
     * @ORM\ManyToMany(targetEntity=ReportGroup::class, inversedBy="reports")
     */
    private $reportGroups;

    public function __construct()
    {
        $this->reportColumns = new ArrayCollection();
        $this->reportGroups = new ArrayCollection();
    }

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

    public function getDashboardType(): ?string
    {
        return $this->dashboardType;
    }

    public function setDashboardType(?string $dashboardType): self
    {
        $this->dashboardType = $dashboardType;

        return $this;
    }

    public function getParticipationType(): ?string
    {
        return $this->participationType;
    }

    public function setParticipationType(?string $participationType): self
    {
        $this->participationType = $participationType;

        return $this;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(?string $registration): self
    {
        $this->registration = $registration;

        return $this;
    }

    public function getExperienceClass(): ?string
    {
        return $this->experienceClass;
    }

    public function setExperienceClass(?string $experienceClass): self
    {
        $this->experienceClass = $experienceClass;

        return $this;
    }

    public function getStudentFirstName(): ?string
    {
        return $this->studentFirstName;
    }

    public function setStudentFirstName(?string $studentFirstName): self
    {
        $this->studentFirstName = $studentFirstName;

        return $this;
    }

    public function getStudentLastName(): ?string
    {
        return $this->studentLastName;
    }

    public function setStudentLastName(?string $studentLastName): self
    {
        $this->studentLastName = $studentLastName;

        return $this;
    }

    public function getProfessionalFirstName(): ?string
    {
        return $this->professionalFirstName;
    }

    public function setProfessionalFirstName(?string $professionalFirstName): self
    {
        $this->professionalFirstName = $professionalFirstName;

        return $this;
    }

    public function getProfessionalLastName(): ?string
    {
        return $this->professionalLastName;
    }

    public function setProfessionalLastName(?string $professionalLastName): self
    {
        $this->professionalLastName = $professionalLastName;

        return $this;
    }

    public function getReportType(): ?string
    {
        return $this->reportType;
    }

    public function setReportType(?string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }

    public function getReportEntityClassName(): ?string
    {
        return $this->reportEntityClassName;
    }

    public function setReportEntityClassName(?string $reportEntityClassName): self
    {
        $this->reportEntityClassName = $reportEntityClassName;

        return $this;
    }

    public function getReportName(): ?string
    {
        return $this->reportName;
    }

    public function setReportName(?string $reportName): self
    {
        $this->reportName = $reportName;

        return $this;
    }

    public function getReportDescription(): ?string
    {
        return $this->reportDescription;
    }

    public function setReportDescription(?string $reportDescription): self
    {
        $this->reportDescription = $reportDescription;

        return $this;
    }

    public function getReportRules(): ?string
    {
        return $this->reportRules;
    }

    public function setReportRules(?string $reportRules): self
    {
        $this->reportRules = $reportRules;

        return $this;
    }

    /**
     * @return Collection|ReportColumn[]
     */
    public function getReportColumns(): Collection
    {
        if (!$this->reportColumns) {
            return new ArrayCollection();
        }

        return $this->reportColumns;
    }

    public function addReportColumn(ReportColumn $reportColumn): self
    {
        if (!$this->reportColumns->contains($reportColumn)) {
            $this->reportColumns[] = $reportColumn;
            $reportColumn->setReport($this);
        }

        return $this;
    }

    public function removeReportColumn(ReportColumn $reportColumn): self
    {
        if ($this->reportColumns->removeElement($reportColumn)) {
            // set the owning side to null (unless already changed)
            if ($reportColumn->getReport() === $this) {
                $reportColumn->setReport(null);
            }
        }

        return $this;
    }

    public function getEntityNameFromEntityClassName($entityClassName)
    {

        if (($entityName = array_search($entityClassName, self::$reportEntityClassNameMap, true)) !== false) {
            return $entityName;
        }

        return $entityClassName;
    }

    public function prepareResult($results): array
    {
        $csv = [];
        foreach ($this->getReportColumns() as $reportColumn) {
            $csv[] = $reportColumn->getUserAlias();
        }
        $csv = [$csv];
        foreach ($results as $row) {
            $csv[] = array_values($row);
        }

        return $csv;
    }

    public function getReportShare(): ?ReportShare
    {
        return $this->reportShare;
    }

    public function setReportShare(?ReportShare $reportShare): self
    {
        // unset the owning side of the relation if necessary
        if ($reportShare === null && $this->reportShare !== null) {
            $this->reportShare->setReport(null);
        }

        // set the owning side of the relation if necessary
        if ($reportShare !== null && $reportShare->getReport() !== $this) {
            $reportShare->setReport($this);
        }

        $this->reportShare = $reportShare;

        return $this;
    }

    /**
     * @return Collection|ReportGroup[]
     */
    public function getReportGroups(): Collection
    {
        return $this->reportGroups;
    }

    public function addReportGroup(ReportGroup $reportGroup): self
    {
        if (!$this->reportGroups->contains($reportGroup)) {
            $this->reportGroups[] = $reportGroup;
        }

        return $this;
    }

    public function removeReportGroup(ReportGroup $reportGroup): self
    {
        $this->reportGroups->removeElement($reportGroup);

        return $this;
    }
}
