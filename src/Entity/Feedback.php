<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FeedbackRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"feedback" = "Feedback", "studentReviewCompanyExperienceFeedback" = "StudentReviewCompanyExperienceFeedback", "studentReviewTeachLessonExperienceFeedback" = "StudentReviewTeachLessonExperienceFeedback", "educatorReviewCompanyExperienceFeedback" = "EducatorReviewCompanyExperienceFeedback", "educatorReviewTeachLessonExperienceFeedback" = "EducatorReviewTeachLessonExperienceFeedback", "professionalReviewStudentToMeetProfessionalFeedback" = "ProfessionalReviewStudentToMeetProfessionalFeedback", "professionalReviewMeetStudentExperienceFeedback" = "ProfessionalReviewMeetStudentExperienceFeedback", "studentReviewMeetProfessionalExperienceFeedback" = "StudentReviewMeetProfessionalExperienceFeedback", "professionalReviewTeachLessonExperienceFeedback" = "ProfessionalReviewTeachLessonExperienceFeedback", "professionalReviewCompanyExperienceFeedback" = "ProfessionalReviewCompanyExperienceFeedback", "studentReviewSchoolExperienceFeedback" = "StudentReviewSchoolExperienceFeedback", "professionalReviewSchoolExperienceFeedback" = "ProfessionalReviewSchoolExperienceFeedback"})
 *
 */
class Feedback
{
    use Timestampable;

    public static $eventTypes = [
        'Company Experience' => 'COMPANY_EXPERIENCE',
        'School Experience' => 'SCHOOL_EXPERIENCE',
        'Guest Topic Instructor' => 'GUEST_TOPIC_INSTRUCTOR',
        'Individual Meeting' => 'INDIVIDUAL_MEETING',
    ];

    /**
     * @Groups({"FEEDBACK"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"FEEDBACK"})
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    protected $rating;

    /**
     * @Groups({"FEEDBACK"})
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $providedCareerInsight = false;

    /**
     * @Groups({"FEEDBACK"})
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $wasEnjoyableAndEngaging = false;

    /**
     * @Groups({"FEEDBACK"})
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $learnSomethingNew = false;

    /**
     * @Groups({"FEEDBACK"})
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    protected $likelihoodToRecommendToFriend = 7;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="text", nullable=true)
     */
    protected $additionalFeedback;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="feedback")
     */
    protected $user;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="feedback")
     */
    protected $experience;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $feedbackProvider;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $interestWorkingForCompany = 0;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $experienceProvider;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\ManyToOne(targetEntity=RolesWillingToFulfill::class)
     */
    protected $experienceType;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $experienceTypeName;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $regions = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $regionNames = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $schools = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $schoolNames = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $companies = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $companyNames = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="date", nullable=true)
     */
    protected $eventStartDate;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $companyAdmins = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $regionalCoordinators = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $schoolAdmins = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $employeeContacts = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $employeeContactNames = [];

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dashboardType;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $relatedToMyClassroomWork;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $topic;

    /**
     * @Groups({"FEEDBACK"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $presenter;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $eventType;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getProvidedCareerInsight(): ?bool
    {
        return $this->providedCareerInsight;
    }

    public function setProvidedCareerInsight(bool $providedCareerInsight): self
    {
        $this->providedCareerInsight = $providedCareerInsight;

        return $this;
    }

    public function getWasEnjoyableAndEngaging(): ?bool
    {
        return $this->wasEnjoyableAndEngaging;
    }

    public function setWasEnjoyableAndEngaging(bool $wasEnjoyableAndEngaging): self
    {
        $this->wasEnjoyableAndEngaging = $wasEnjoyableAndEngaging;

        return $this;
    }

    public function getLearnSomethingNew(): ?bool
    {
        return $this->learnSomethingNew;
    }

    public function setLearnSomethingNew(bool $learnSomethingNew): self
    {
        $this->learnSomethingNew = $learnSomethingNew;

        return $this;
    }

    public function getLikelihoodToRecommendToFriend(): ?int
    {
        return $this->likelihoodToRecommendToFriend;
    }

    public function setLikelihoodToRecommendToFriend(int $likelihoodToRecommendToFriend): self
    {
        $this->likelihoodToRecommendToFriend = $likelihoodToRecommendToFriend;

        return $this;
    }

    public function getAdditionalFeedback(): ?string
    {
        return $this->additionalFeedback;
    }

    public function setAdditionalFeedback(?string $additionalFeedback): self
    {
        $this->additionalFeedback = $additionalFeedback;

        return $this;
    }

    /**
     * @Groups({"FEEDBACK"})
     * @return string
     * @throws \ReflectionException
     */
    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

        return $this;
    }


    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getFeedbackProvider(): ?string
    {
        return $this->feedbackProvider;
    }

    public function setFeedbackProvider(?string $feedbackProvider): self
    {
        $this->feedbackProvider = $feedbackProvider;

        return $this;
    }

    public function getInterestWorkingForCompany(): ?int
    {
        return $this->interestWorkingForCompany;
    }

    public function setInterestWorkingForCompany(?int $interestWorkingForCompany): self
    {
        $this->interestWorkingForCompany = $interestWorkingForCompany;

        return $this;
    }

    public function getExperienceProvider(): ?string
    {
        return $this->experienceProvider;
    }

    public function setExperienceProvider(?string $experienceProvider): self
    {
        $this->experienceProvider = $experienceProvider;

        return $this;
    }

    public function getExperienceType(): ?RolesWillingToFulfill
    {
        return $this->experienceType;
    }

    public function setExperienceType(?RolesWillingToFulfill $experienceType): self
    {
        $this->experienceType = $experienceType;

        return $this;
    }

    public function getExperienceTypeName(): ?string
    {
        return $this->experienceTypeName;
    }

    public function setExperienceTypeName(?string $experienceTypeName): self
    {
        $this->experienceTypeName = $experienceTypeName;

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

    public function getCompanies(): ?array
    {
        return $this->companies;
    }

    public function setCompanies(?array $companies): self
    {
        $this->companies = array_values(array_unique($companies));

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

    public function getEventStartDate(): ?\DateTimeInterface
    {
        return $this->eventStartDate;
    }

    public function setEventStartDate(?\DateTimeInterface $eventStartDate): self
    {
        $this->eventStartDate = $eventStartDate;

        return $this;
    }

    public function getCompanyAdmins(): ?array
    {
        return $this->companyAdmins;
    }

    public function setCompanyAdmins(?array $companyAdmins): self
    {
        $this->companyAdmins = array_values(array_unique($companyAdmins));

        return $this;
    }

    public function getRegionalCoordinators(): ?array
    {
        return $this->regionalCoordinators;
    }

    public function setRegionalCoordinators(?array $regionalCoordinators): self
    {
        $this->regionalCoordinators = array_values(array_unique($regionalCoordinators));

        return $this;
    }

    public function getSchoolAdmins(): ?array
    {
        return $this->schoolAdmins;
    }

    public function setSchoolAdmins(?array $schoolAdmins): self
    {
        $this->schoolAdmins = array_values(array_unique($schoolAdmins));

        return $this;
    }

    public function getEmployeeContacts(): ?array
    {
        return $this->employeeContacts;
    }

    public function setEmployeeContacts(?array $employeeContacts): self
    {
        $this->employeeContacts = array_values(array_unique($employeeContacts));

        return $this;
    }

    public function getEmployeeContactNames(): ?array
    {
        return $this->employeeContactNames;
    }

    public function setEmployeeContactNames(?array $employeeContactNames): self
    {
        $this->employeeContactNames = $employeeContactNames;

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

    public function getRelatedToMyClassroomWork(): ?bool
    {
        return $this->relatedToMyClassroomWork;
    }

    public function setRelatedToMyClassroomWork(?bool $relatedToMyClassroomWork): self
    {
        $this->relatedToMyClassroomWork = $relatedToMyClassroomWork;

        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(?string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getPresenter(): ?string
    {
        return $this->presenter;
    }

    public function setPresenter(?string $presenter): self
    {
        $this->presenter = $presenter;

        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(?string $eventType): self
    {
        $this->eventType = $eventType;

        return $this;
    }
}
