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
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    protected $rating = 0;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $providedCareerInsight = false;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $wasEnjoyableAndEngaging = false;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $learnSomethingNew = false;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    protected $likelihoodToRecommendToFriend = 7;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $additionalFeedback;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="feedback")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="feedback")
     */
    protected $experience;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $feedbackProvider;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $interestWorkingForCompany = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceProvider;

    /**
     * @ORM\ManyToOne(targetEntity=RolesWillingToFulfill::class)
     */
    private $experienceType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceTypeName;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $regions = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $regionNames = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $schools = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $schoolNames = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $companies = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $companyNames = [];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $eventStartDate;


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
        $this->schools = $schools;

        return $this;
    }

    public function getSchoolNames(): ?array
    {
        return $this->schoolNames;
    }

    public function setSchoolNames(?array $schoolNames): self
    {
        $this->schoolNames = $schoolNames;

        return $this;
    }

    public function getCompanies(): ?array
    {
        return $this->companies;
    }

    public function setCompanies(?array $companies): self
    {
        $this->companies = $companies;

        return $this;
    }

    public function getCompanyNames(): ?array
    {
        return $this->companyNames;
    }

    public function setCompanyNames(?array $companyNames): self
    {
        $this->companyNames = $companyNames;

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
}
