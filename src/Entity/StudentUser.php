<?php

namespace App\Entity;

use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentUserRepository")
 * @UniqueEntity(
 *     fields={"school", "studentId"},
 *     errorPath="studentId",
 *     message="This student Id already belongs to another user at this school",
 *     groups={"STUDENT_USER"}
 * )
 *
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"STUDENT_USER"}, repositoryMethod="findByUniqueCriteria")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username", groups={"STUDENT_USER"}, repositoryMethod="findByUniqueCriteria")
 */
class StudentUser extends User
{
    use RandomStringGenerator;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="studentUsers")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $school;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grade", inversedBy="studentUsers")
     */
    private $grade;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolEmail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $briefBio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayName;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="studentUsers", cascade={"persist", "remove"})
     */
    private $secondaryIndustries;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", inversedBy="studentUsers")
     */
    private $companiesInterestedIn;

    /**
     * @Groups({"STUDENT_USER"})
     * @ORM\Column(type="string", length=255)
     */
    private $studentId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="studentUsers")
     */
    private $site;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\EducatorUser", mappedBy="studentUsers")
     */
    private $educatorUsers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EducatorRegisterStudentForCompanyExperienceRequest", inversedBy="studentUsers")
     */
    private $educatorRegisterStudentForCompanyExperienceRequest;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\EducatorRegisterStudentForCompanyExperienceRequest", mappedBy="studentUsers")
     */
    private $educatorRegisterStudentForCompanyExperienceRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyExperienceStudentExpressInterestRequest", mappedBy="studentUser")
     */
    private $companyExperienceStudentExpressInterestRequests;

    /**
     * @ORM\OneToMany(targetEntity="StudentReviewCompanyExperienceFeedback", mappedBy="student", orphanRemoval=true)
     */
    private $studentReviewExperienceFeedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentReviewTeachLessonExperienceFeedback", mappedBy="student", orphanRemoval=true)
     */
    private $studentReviewTeachLessonExperienceFeedback;

    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries = new ArrayCollection();
        $this->companiesInterestedIn = new ArrayCollection();
        $this->educatorUsers = new ArrayCollection();
        $this->educatorRegisterStudentForCompanyExperienceRequests = new ArrayCollection();
        $this->companyExperienceStudentExpressInterestRequests = new ArrayCollection();
        $this->studentReviewExperienceFeedback = new ArrayCollection();
        $this->studentReviewTeachLessonExperienceFeedback = new ArrayCollection();
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

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getSchoolEmail(): ?string
    {
        return $this->schoolEmail;
    }

    public function setSchoolEmail(?string $schoolEmail): self
    {
        $this->schoolEmail = $schoolEmail;

        return $this;
    }

    public function getBriefBio(): ?string
    {
        return $this->briefBio;
    }

    public function setBriefBio(?string $briefBio): self
    {
        $this->briefBio = $briefBio;

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
        }

        return $this;
    }

    public function removeSecondaryIndustry(SecondaryIndustry $secondaryIndustry): self
    {
        if ($this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries->removeElement($secondaryIndustry);
        }

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompaniesInterestedIn(): Collection
    {
        return $this->companiesInterestedIn;
    }

    public function addCompaniesInterestedIn(Company $companiesInterestedIn): self
    {
        if (!$this->companiesInterestedIn->contains($companiesInterestedIn)) {
            $this->companiesInterestedIn[] = $companiesInterestedIn;
        }

        return $this;
    }

    public function removeCompaniesInterestedIn(Company $companiesInterestedIn): self
    {
        if ($this->companiesInterestedIn->contains($companiesInterestedIn)) {
            $this->companiesInterestedIn->removeElement($companiesInterestedIn);
        }

        return $this;
    }

    public function getStudentId(): ?string
    {
        return $this->studentId;
    }

    public function setStudentId(string $studentId): self
    {
        $this->studentId = $studentId;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * first name - period - last name
     * @return string
     */
    public function getTempUsername() {
        return strtolower(sprintf("%s.%s",
                $this->firstName,
                $this->lastName
        ));
    }

    /**
     * first 3 letters of last name followed by their unique student ID
     * followed by an explanation point
     *
     * @return string
     */
    public function getTempPassword() {
        return strtolower(sprintf("%s%s!",
            substr($this->lastName, 0, 3),
            $this->getStudentId()
        ));
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection|EducatorUser[]
     */
    public function getEducatorUsers(): Collection
    {
        return $this->educatorUsers;
    }

    public function addEducatorUser(EducatorUser $educatorUser): self
    {
        if (!$this->educatorUsers->contains($educatorUser)) {
            $this->educatorUsers[] = $educatorUser;
            $educatorUser->addStudentUser($this);
        }

        return $this;
    }

    public function removeEducatorUser(EducatorUser $educatorUser): self
    {
        if ($this->educatorUsers->contains($educatorUser)) {
            $this->educatorUsers->removeElement($educatorUser);
            $educatorUser->removeStudentUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|EducatorRegisterStudentForCompanyExperienceRequest[]
     */
    public function getEducatorRegisterStudentForCompanyExperienceRequests(): Collection
    {
        return $this->educatorRegisterStudentForCompanyExperienceRequests;
    }

    public function addEducatorRegisterStudentForCompanyExperienceRequest(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest): self
    {
        if (!$this->educatorRegisterStudentForCompanyExperienceRequests->contains($educatorRegisterStudentForCompanyExperienceRequest)) {
            $this->educatorRegisterStudentForCompanyExperienceRequests[] = $educatorRegisterStudentForCompanyExperienceRequest;
            $educatorRegisterStudentForCompanyExperienceRequest->addStudentUser($this);
        }

        return $this;
    }

    public function removeEducatorRegisterStudentForCompanyExperienceRequest(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest): self
    {
        if ($this->educatorRegisterStudentForCompanyExperienceRequests->contains($educatorRegisterStudentForCompanyExperienceRequest)) {
            $this->educatorRegisterStudentForCompanyExperienceRequests->removeElement($educatorRegisterStudentForCompanyExperienceRequest);
            $educatorRegisterStudentForCompanyExperienceRequest->removeStudentUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|CompanyExperienceStudentExpressInterestRequest[]
     */
    public function getCompanyExperienceStudentExpressInterestRequests(): Collection
    {
        return $this->companyExperienceStudentExpressInterestRequests;
    }

    public function addCompanyExperienceStudentExpressInterestRequest(CompanyExperienceStudentExpressInterestRequest $companyExperienceStudentExpressInterestRequest): self
    {
        if (!$this->companyExperienceStudentExpressInterestRequests->contains($companyExperienceStudentExpressInterestRequest)) {
            $this->companyExperienceStudentExpressInterestRequests[] = $companyExperienceStudentExpressInterestRequest;
            $companyExperienceStudentExpressInterestRequest->setStudentUser($this);
        }

        return $this;
    }

    public function removeCompanyExperienceStudentExpressInterestRequest(CompanyExperienceStudentExpressInterestRequest $companyExperienceStudentExpressInterestRequest): self
    {
        if ($this->companyExperienceStudentExpressInterestRequests->contains($companyExperienceStudentExpressInterestRequest)) {
            $this->companyExperienceStudentExpressInterestRequests->removeElement($companyExperienceStudentExpressInterestRequest);
            // set the owning side to null (unless already changed)
            if ($companyExperienceStudentExpressInterestRequest->getStudentUser() === $this) {
                $companyExperienceStudentExpressInterestRequest->setStudentUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StudentReviewCompanyExperienceFeedback[]
     */
    public function getStudentReviewExperienceFeedback(): Collection
    {
        return $this->studentReviewExperienceFeedback;
    }

    public function addStudentReviewExperienceFeedback(StudentReviewCompanyExperienceFeedback $studentReviewExperienceFeedback): self
    {
        if (!$this->studentReviewExperienceFeedback->contains($studentReviewExperienceFeedback)) {
            $this->studentReviewExperienceFeedback[] = $studentReviewExperienceFeedback;
            $studentReviewExperienceFeedback->setStudent($this);
        }

        return $this;
    }

    public function removeStudentReviewExperienceFeedback(StudentReviewCompanyExperienceFeedback $studentReviewExperienceFeedback): self
    {
        if ($this->studentReviewExperienceFeedback->contains($studentReviewExperienceFeedback)) {
            $this->studentReviewExperienceFeedback->removeElement($studentReviewExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($studentReviewExperienceFeedback->getStudent() === $this) {
                $studentReviewExperienceFeedback->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StudentReviewTeachLessonExperienceFeedback[]
     */
    public function getStudentReviewTeachLessonExperienceFeedback(): Collection
    {
        return $this->studentReviewTeachLessonExperienceFeedback;
    }

    public function addStudentReviewTeachLessonExperienceFeedback(StudentReviewTeachLessonExperienceFeedback $studentReviewTeachLessonExperienceFeedback): self
    {
        if (!$this->studentReviewTeachLessonExperienceFeedback->contains($studentReviewTeachLessonExperienceFeedback)) {
            $this->studentReviewTeachLessonExperienceFeedback[] = $studentReviewTeachLessonExperienceFeedback;
            $studentReviewTeachLessonExperienceFeedback->setStudent($this);
        }

        return $this;
    }

    public function removeStudentReviewTeachLessonExperienceFeedback(StudentReviewTeachLessonExperienceFeedback $studentReviewTeachLessonExperienceFeedback): self
    {
        if ($this->studentReviewTeachLessonExperienceFeedback->contains($studentReviewTeachLessonExperienceFeedback)) {
            $this->studentReviewTeachLessonExperienceFeedback->removeElement($studentReviewTeachLessonExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($studentReviewTeachLessonExperienceFeedback->getStudent() === $this) {
                $studentReviewTeachLessonExperienceFeedback->setStudent(null);
            }
        }

        return $this;
    }
}
