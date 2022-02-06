<?php

namespace App\Entity;

use App\Util\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducatorUserRepository")
 */
class EducatorUser extends User
{
    use RandomStringGenerator;

    /**
     * @Groups({"EDUCATOR_USER_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="educatorUsers")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $school;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneExt;

    /**
     * @Groups({"EDUCATOR_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $briefBio;

    /**
     * @Assert\NotBlank(message="Please enter a brief bio", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $linkedinProfile;

    /**
     * @Groups({"EDUCATOR_USER_DATA"})
     *
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select your profession(s)",
     *     groups={"EDUCATOR_PROFILE_PERSONAL"}
     * )
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="educatorUsers")
     */
    private $secondaryIndustries;

    /**
     * @Groups({"EDUCATOR_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $interests;

    /**
     * @Assert\NotBlank(message="Don't forget a display name", groups={"EDUCATOR_USER", "EDUCATOR_PROFILE_PERSONAL"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="educatorUsers")
     */
    private $site;

    /**
     *
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select your students",
     *     groups={"EDUCATOR_PROFILE_STUDENT"}
     * )
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\StudentUser", inversedBy="educatorUsers", cascade={"persist"})
     */
    private $studentUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorReviewCompanyExperienceFeedback", mappedBy="educator")
     */
    private $educatorReviewCompanyExperienceFeedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorReviewTeachLessonExperienceFeedback", mappedBy="educator")
     */
    private $educatorReviewTeachLessonExperienceFeedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorRegisterEducatorForCompanyExperienceRequest", mappedBy="educatorUser")
     */
    private $educatorRegisterEducatorForCompanyExperienceRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorVideo", mappedBy="educator")
     */
    private $educatorVideos;

    /**
     * @Groups({"EDUCATOR_USER_DATA"})
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select your course(s), club(s), positions(s)",
     *     groups={"EDUCATOR_PROFILE_PERSONAL"}
     * )
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Course", inversedBy="educatorUsers")
     */
    private $myCourses;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reportSchool;

    /**
     *  @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select at least one career sector",
     *     groups={"EDUCATOR_PROFILE_PERSONAL"}
     * )
     *
     * @ORM\ManyToMany(targetEntity=Industry::class, inversedBy="educatorUsers")
     */
    private $primaryIndustries;

    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries                         = new ArrayCollection();
        $this->studentUsers                                = new ArrayCollection();
        $this->educatorReviewCompanyExperienceFeedback     = new ArrayCollection();
        $this->educatorReviewTeachLessonExperienceFeedback = new ArrayCollection();
        $this->myCourses                                   = new ArrayCollection();
        $this->educatorVideos                              = new ArrayCollection();
        $this->primaryIndustries                           = new ArrayCollection();
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhoneExt(): ?string
    {
        return $this->phoneExt;
    }

    public function setPhoneExt(?string $phoneExt): self
    {
        $this->phoneExt = $phoneExt;

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

    public function getLinkedinProfile(): ?string
    {
        return $this->linkedinProfile;
    }

    public function setLinkedinProfile(?string $linkedinProfile): self
    {
        $this->linkedinProfile = $linkedinProfile;

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

    public function getInterests(): ?string
    {
        return $this->interests;
    }

    public function setInterests(?string $interests): self
    {
        $this->interests = $interests;

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
     * first name - period - last name (- period - random string)
     *
     * @return string
     */
    public function getTempUsername($similarUsernameCount)
    {

        if ($similarUsernameCount) {
            return strtolower(
                sprintf(
                    "%s.%s.%s",
                    $this->firstName,
                    $this->lastName,
                    $similarUsernameCount
                )
            );
        } else {
            return strtolower(
                sprintf(
                    "%s.%s",
                    $this->firstName,
                    $this->lastName
                )
            );
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
     * @return Collection|StudentUser[]
     */
    public function getAlphabeticallySortedStudentUsers(): Collection
    {
        $students     = array ();
        $student_list = $this->studentUsers->toArray();
        foreach ($student_list as $student) {
            if ($student->activated == 1) {
                array_push($students, $student);
            }
        }

        usort(
            $students, function ($a, $b) {
            return strcmp($a->lastName, $b->lastName);
        }
        );

        return new ArrayCollection($students);
    }

    /**
     * @return Collection|StudentUser[]
     */
    public function getActiveStudentUsers(): Collection
    {
        $students = $this->getAlphabeticallySortedStudentUsers();

        $activeStudents = new ArrayCollection();
        /** @var StudentUser $studentUser */
        foreach ($students as $studentUser) {

            if (!$studentUser->getActivated()) {
                continue;
            }

            $activeStudents->add($studentUser);
        }

        return $activeStudents;
    }

    /**
     * @return Collection|StudentUser[]
     */
    public function getStudentUsers(): Collection
    {
        return $this->studentUsers;
    }

    public function addStudentUser(StudentUser $studentUser): self
    {
        if (!$this->studentUsers->contains($studentUser)) {
            $this->studentUsers[] = $studentUser;
        }

        return $this;
    }

    public function removeStudentUser(StudentUser $studentUser): self
    {
        if ($this->studentUsers->contains($studentUser)) {
            $this->studentUsers->removeElement($studentUser);
        }

        return $this;
    }

    public function hasStudentUserInClass(StudentUser $studentUserToCheck)
    {
        foreach ($this->getStudentUsers() as $studentUser) {
            if ($studentUserToCheck->getId() === $studentUser->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|EducatorReviewCompanyExperienceFeedback[]
     */
    public function getEducatorReviewCompanyExperienceFeedback(): Collection
    {
        return $this->educatorReviewCompanyExperienceFeedback;
    }

    public function addEducatorReviewCompanyExperienceFeedback(
        EducatorReviewCompanyExperienceFeedback $educatorReviewCompanyExperienceFeedback
    ): self {
        if (!$this->educatorReviewCompanyExperienceFeedback->contains($educatorReviewCompanyExperienceFeedback)) {
            $this->educatorReviewCompanyExperienceFeedback[] = $educatorReviewCompanyExperienceFeedback;
            $educatorReviewCompanyExperienceFeedback->setEducator($this);
        }

        return $this;
    }

    public function removeEducatorReviewCompanyExperienceFeedback(
        EducatorReviewCompanyExperienceFeedback $educatorReviewCompanyExperienceFeedback
    ): self {
        if ($this->educatorReviewCompanyExperienceFeedback->contains($educatorReviewCompanyExperienceFeedback)) {
            $this->educatorReviewCompanyExperienceFeedback->removeElement($educatorReviewCompanyExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($educatorReviewCompanyExperienceFeedback->getEducator() === $this) {
                $educatorReviewCompanyExperienceFeedback->setEducator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EducatorReviewTeachLessonExperienceFeedback[]
     */
    public function getEducatorReviewTeachLessonExperienceFeedback(): Collection
    {
        return $this->educatorReviewTeachLessonExperienceFeedback;
    }

    public function addEducatorReviewTeachLessonExperienceFeedback(
        EducatorReviewTeachLessonExperienceFeedback $educatorReviewTeachLessonExperienceFeedback
    ): self {
        if (!$this->educatorReviewTeachLessonExperienceFeedback->contains($educatorReviewTeachLessonExperienceFeedback)) {
            $this->educatorReviewTeachLessonExperienceFeedback[] = $educatorReviewTeachLessonExperienceFeedback;
            $educatorReviewTeachLessonExperienceFeedback->setEducator($this);
        }

        return $this;
    }

    public function removeEducatorReviewTeachLessonExperienceFeedback(
        EducatorReviewTeachLessonExperienceFeedback $educatorReviewTeachLessonExperienceFeedback
    ): self {
        if ($this->educatorReviewTeachLessonExperienceFeedback->contains($educatorReviewTeachLessonExperienceFeedback)) {
            $this->educatorReviewTeachLessonExperienceFeedback->removeElement($educatorReviewTeachLessonExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($educatorReviewTeachLessonExperienceFeedback->getEducator() === $this) {
                $educatorReviewTeachLessonExperienceFeedback->setEducator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Course[]
     */
    public function getMyCourses(): Collection
    {
        return $this->myCourses;
    }

    public function addMyCourse(Course $myCourse): self
    {
        if (!$this->myCourses->contains($myCourse)) {
            $this->myCourses[] = $myCourse;
        }

        return $this;
    }

    public function removeMyCourse(Course $myCourse): self
    {
        if ($this->myCourses->contains($myCourse)) {
            $this->myCourses->removeElement($myCourse);
        }

        return $this;
    }


    /**
     * @return Collection|EducatorVideo[]
     */
    public function getEducatorVideos(): Collection
    {
        return $this->educatorVideos;
    }

    public function addEducatorVideo(EducatorVideo $educatorVideo): self
    {
        if (!$this->educatorVideos->contains($educatorVideo)) {
            $this->educatorVideos[] = $educatorVideo;
            $educatorVideo->setEducator($this);
        }

        return $this;
    }

    public function removeEducatorVideo(EducatorVideo $educatorVideo): self
    {
        if ($this->educatorVideos->contains($educatorVideo)) {
            $this->educatorVideos->removeElement($educatorVideo);
            // set the owning side to null (unless already changed)
            if ($educatorVideo->getEducator() === $this) {
                $educatorVideo->setEducator(null);
            }
        }

        return $this;
    }

    public function getReportSchool(): ?string
    {
        return $this->reportSchool;
    }

    public function setReportSchool(?string $reportSchool): self
    {
        $this->reportSchool = $reportSchool;

        return $this;
    }

    /**
     * @return Collection|Industry[]
     */
    public function getPrimaryIndustries(): Collection
    {
        return $this->primaryIndustries;
    }

    public function addPrimaryIndustry(Industry $primaryIndustry): self
    {
        if (!$this->primaryIndustries->contains($primaryIndustry)) {
            $this->primaryIndustries[] = $primaryIndustry;
        }

        return $this;
    }

    public function removePrimaryIndustry(Industry $primaryIndustry): self
    {
        $this->primaryIndustries->removeElement($primaryIndustry);

        return $this;
    }

    public function getPotentialSplashPages() {
        return ['educator-welcome'];
    }
}
