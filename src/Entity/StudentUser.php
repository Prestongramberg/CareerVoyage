<?php

namespace App\Entity;

use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "Please select at least one career field",
     *     groups={"EDIT"}
     * )
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="studentUsers", cascade={"persist", "remove"})
     */
    private $secondaryIndustries;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", inversedBy="studentUsers")
     */
    private $companiesInterestedIn;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @ORM\OneToMany(targetEntity="StudentReviewCompanyExperienceFeedback", mappedBy="student", orphanRemoval=true)
     */
    private $studentReviewExperienceFeedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentReviewTeachLessonExperienceFeedback", mappedBy="student", orphanRemoval=true)
     */
    private $studentReviewTeachLessonExperienceFeedback;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $graduatingYear;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorRegisterStudentForCompanyExperienceRequest", mappedBy="studentUser")
     */
    private $educatorRegisterStudentForCompanyExperienceRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentToMeetProfessionalRequest", mappedBy="student")
     */
    private $studentToMeetProfessionalRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AllowedCommunication", mappedBy="studentUser")
     */
    private $allowedCommunications;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $careerStatement;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archived = false;

    /**
     * @var string
     */
    private $educatorNumber;

    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries = new ArrayCollection();
        $this->companiesInterestedIn = new ArrayCollection();
        $this->educatorUsers = new ArrayCollection();
        $this->studentReviewExperienceFeedback = new ArrayCollection();
        $this->studentReviewTeachLessonExperienceFeedback = new ArrayCollection();
        $this->educatorRegisterStudentForCompanyExperienceRequests = new ArrayCollection();
        $this->studentToMeetProfessionalRequests = new ArrayCollection();
        $this->allowedCommunications = new ArrayCollection();
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

    public function getCareerStatement(): ?string
    {
        return $this->careerStatement;
    }

    public function setCareerStatement(?string $careerStatement): self
    {
        $this->careerStatement = $careerStatement;

        return $this;
    }

    /**
     * first name - period - last name (- period - random string)
     * @return string
     */
    public function getTempUsername($similarUsernameCount) {

        if ($similarUsernameCount) {
            return strtolower(sprintf("%s.%s.%s",
                $this->firstName,
                $this->lastName,
                $similarUsernameCount
            ));
        } else {
            return strtolower(sprintf("%s.%s",
                $this->firstName,
                $this->lastName
            ));
        }
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
            $educatorRegisterStudentForCompanyExperienceRequest->setStudentUser($this);
        }

        return $this;
    }

    public function removeEducatorRegisterStudentForCompanyExperienceRequest(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest): self
    {
        if ($this->educatorRegisterStudentForCompanyExperienceRequests->contains($educatorRegisterStudentForCompanyExperienceRequest)) {
            $this->educatorRegisterStudentForCompanyExperienceRequests->removeElement($educatorRegisterStudentForCompanyExperienceRequest);
            // set the owning side to null (unless already changed)
            if ($educatorRegisterStudentForCompanyExperienceRequest->getStudentUser() === $this) {
                $educatorRegisterStudentForCompanyExperienceRequest->setStudentUser(null);
            }
        }

        return $this;
    }

    public function isCommunicationEnabled() {
        if($this->getSchool() && $this->getSchool()->getCommunicationType() !== null && $this->getSchool()->getCommunicationType() !== School::COMMUNICATION_TYPE_DEFAULT) {
            return true;
        }
        return false;
    }

    public function isTeacherApprovalRequired() {
        if($this->getSchool() && $this->getSchool()->getCommunicationType() !== null && $this->getSchool()->getCommunicationType() === School::COMMUNICATION_TYPE_TEACHER_APPROVAL_REQUIRED) {
            return true;
        }
        return false;
    }

    public function isTeacherApprovalNotRequired() {
        if($this->getSchool() && $this->getSchool()->getCommunicationType() !== null && $this->getSchool()->getCommunicationType() === School::COMMUNICATION_TYPE_TEACHER_APPROVAL_NOT_REQUIRED) {
            return true;
        }
        return false;
    }

    /**
     * @return Collection|StudentToMeetProfessionalRequest[]
     */
    public function getStudentToMeetProfessionalRequests(): Collection
    {
        return $this->studentToMeetProfessionalRequests;
    }

    public function addStudentToMeetProfessionalRequest(StudentToMeetProfessionalRequest $studentToMeetProfessionalRequest): self
    {
        if (!$this->studentToMeetProfessionalRequests->contains($studentToMeetProfessionalRequest)) {
            $this->studentToMeetProfessionalRequests[] = $studentToMeetProfessionalRequest;
            $studentToMeetProfessionalRequest->setStudent($this);
        }

        return $this;
    }

    public function removeStudentToMeetProfessionalRequest(StudentToMeetProfessionalRequest $studentToMeetProfessionalRequest): self
    {
        if ($this->studentToMeetProfessionalRequests->contains($studentToMeetProfessionalRequest)) {
            $this->studentToMeetProfessionalRequests->removeElement($studentToMeetProfessionalRequest);
            // set the owning side to null (unless already changed)
            if ($studentToMeetProfessionalRequest->getStudent() === $this) {
                $studentToMeetProfessionalRequest->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AllowedCommunication[]
     */
    public function getAllowedCommunications(): Collection
    {
        return $this->allowedCommunications;
    }

    public function addAllowedCommunication(AllowedCommunication $allowedCommunication): self
    {
        if (!$this->allowedCommunications->contains($allowedCommunication)) {
            $this->allowedCommunications[] = $allowedCommunication;
            $allowedCommunication->setStudentUser($this);
        }

        return $this;
    }

    public function removeAllowedCommunication(AllowedCommunication $allowedCommunication): self
    {
        if ($this->allowedCommunications->contains($allowedCommunication)) {
            $this->allowedCommunications->removeElement($allowedCommunication);
            // set the owning side to null (unless already changed)
            if ($allowedCommunication->getStudentUser() === $this) {
                $allowedCommunication->setStudentUser(null);
            }
        }

        return $this;
    }

    public function getArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(?bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @Groups({"STUDENT_USER"})
     */
    public function getEducatorNumber()
    {
        return $this->educatorNumber;
    }

    public function setEducatorNumber($educatorNumber)
    {
        $this->educatorNumber = $educatorNumber;
    }
}
