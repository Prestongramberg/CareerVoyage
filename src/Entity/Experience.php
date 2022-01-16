<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Validator\Constraints as CustomAssert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @CustomAssert\ExperienceDetails(groups={"CREATE", "EDIT", "SCHOOL_EXPERIENCE"})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"companyExperience" = "CompanyExperience", "schoolExperience" = "SchoolExperience", "teachLessonExperience" = "TeachLessonExperience", "studentToMeetProfessionalExperience" = "StudentToMeetProfessionalExperience"})
 */
abstract class Experience
{
    use Timestampable;

    public static $types
        = [
            'Site Visit' => 'SITE_VISIT',
            'Event'      => 'EVENT',
            'Externship' => 'EXTERNSHIP',
            'Internship' => 'INTERNSHIP',
            'Job'        => 'JOB',
        ];

    public static $paymentTypes
        = [
            'Per Person And Per Visit' => 'PER_PERSON_AND_PER_VISIT',
            'Hour'                     => 'HOUR',
            'Day'                      => 'DAY',
            'Week'                     => 'WEEK',
            'Month'                    => 'MONTH',
            'Year'                     => 'YEAR',
        ];

    public static $requireApprovalChoices
        = [
            'No'  => false,
            'Yes' => true,
        ];

    /**
     * @Assert\Callback(groups={"CREATE"})
     * @param  ExecutionContextInterface  $context
     * @param                             $payload
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (!$this->payment) {
            if (!is_float($this->payment) && !is_numeric($this->payment)) {
                $context->buildViolation(
                    'You must enter a valid number or decimal for the payment!'
                )->atPath('payment')->addViolation();
            }
        }
    }

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA", "FEEDBACK"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please add a title for the experience.", groups={"EXPERIENCE"})
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    protected $briefDescription;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please add a description for the experience.", groups={"EXPERIENCE"})
     * @ORM\Column(type="text", nullable=true)
     */
    protected $about;

    /**
     * @Assert\NotNull(message="Don't forget a total number of available spaces!", groups={"CREATE", "EDIT"})
     * @Assert\PositiveOrZero(message="Don't forget a total number of available spaces!", groups={"CREATE", "EDIT"})
     * @Groups({"EXPERIENCE_DATA", "CREATE", "EDIT"})
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $availableSpaces;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $payment = 0;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $paymentShownIsPer;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $phoneNumber;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $website;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $street;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $zipcode;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startDateAndTime;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDateAndTime;


    /**
     * @Groups({"EXPERIENCE_DATA"})
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\OneToMany(targetEntity="App\Entity\ExperienceFile", mappedBy="experience")
     */
    protected $experienceFiles;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="experiences")
     */
    protected $state;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please choose an event type.", groups={"EXPERIENCE"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RolesWillingToFulfill", inversedBy="experiences")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $type;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="experiences")
     */
    protected $secondaryIndustries;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\OneToMany(targetEntity="App\Entity\registration", mappedBy="experience")
     */
    protected $registrations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Feedback", mappedBy="experience")
     */
    protected $feedback;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $latitude;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $longitude;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $cancelled = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $requireApproval = false;

    /**
     * @ORM\OneToMany(targetEntity=Share::class, mappedBy="experience")
     */
    protected $shares;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $npsScore;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $totalResponses;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $averageRating;

    /**
     * @ORM\OneToOne(targetEntity=Request::class, inversedBy="experience")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $request;

    /**
     * @Assert\NotBlank(message="Please enter a valid address for your experience.", groups={"EXPERIENCE"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $addressSearch;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $timezone = 'America/Chicago';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $utcStartDateAndTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $utcEndDateAndTime;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="experiences")
     */
    protected $tags;

    /**
     * @ORM\OneToMany(targetEntity=ExperienceResource::class, mappedBy="experience")
     */
    protected $experienceResources;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isRecurring = false;

    protected $startDate;

    protected $startTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $recurrenceRule;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    protected $schedule = [];

    /**
     * @ORM\ManyToOne(targetEntity=Experience::class, inversedBy="childEvents")
     */
    protected $parentEvent;

    /**
     * @ORM\OneToMany(targetEntity=Experience::class, mappedBy="parentEvent")
     */
    protected $childEvents;

    public function __construct()
    {
        $this->experienceFiles     = new ArrayCollection();
        $this->secondaryIndustries = new ArrayCollection();
        $this->registrations       = new ArrayCollection();
        $this->feedback            = new ArrayCollection();
        $this->shares              = new ArrayCollection();
        $this->tags                = new ArrayCollection();
        $this->experienceResources = new ArrayCollection();
        $this->childEvents = new ArrayCollection();
    }

    public function isVirtual()
    {
        if (stripos(strtolower($this->getType()->getName()), 'virtual')
            !== false
        ) {
            return true;
        }

        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getBriefDescription()
    {
        return $this->briefDescription;
    }

    public function setBriefDescription($briefDescription)
    {
        $this->briefDescription = $briefDescription;

        return $this;
    }

    public function getAbout()
    {
        return $this->about;
    }

    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    public function getAvailableSpaces()
    {
        return $this->availableSpaces;
    }

    public function setAvailableSpaces($availableSpaces)
    {
        $this->availableSpaces = $availableSpaces;

        return $this;
    }

    public function getPayment()
    {
        return $this->payment;
    }

    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }

    public function getPaymentShownIsPer()
    {
        return $this->paymentShownIsPer;
    }

    public function setPaymentShownIsPer(?string $paymentShownIsPer)
    {
        $this->paymentShownIsPer = $paymentShownIsPer;

        return $this;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street)
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city)
    {
        $this->city = $city;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getStartDateAndTime()
    {
        return $this->startDateAndTime;
    }

    public function setStartDateAndTime($startDateAndTime)
    {
        $this->startDateAndTime = $startDateAndTime;

        return $this;
    }

    public function getEndDateAndTime()
    {
        return $this->endDateAndTime;
    }

    public function setEndDateAndTime($endDateAndTime)
    {
        $this->endDateAndTime = $endDateAndTime;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|ExperienceFile[]
     */
    public function getExperienceFiles(): Collection
    {
        return $this->experienceFiles;
    }

    public function addExperienceFile(ExperienceFile $experienceFile): self
    {
        if (!$this->experienceFiles->contains($experienceFile)) {
            $this->experienceFiles[] = $experienceFile;
            $experienceFile->setExperience($this);
        }

        return $this;
    }

    public function removeExperienceFile(ExperienceFile $experienceFile): self
    {
        if ($this->experienceFiles->contains($experienceFile)) {
            $this->experienceFiles->removeElement($experienceFile);
            // set the owning side to null (unless already changed)
            if ($experienceFile->getExperience() === $this) {
                $experienceFile->setExperience(null);
            }
        }

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getType(): ?RolesWillingToFulfill
    {
        return $this->type;
    }

    public function setType(?RolesWillingToFulfill $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|SecondaryIndustry[]
     */
    public function getSecondaryIndustries(): Collection
    {
        return $this->secondaryIndustries;
    }

    public function addSecondaryIndustry(SecondaryIndustry $secondaryIndustry
    ): self {
        if (!$this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries[] = $secondaryIndustry;
        }

        return $this;
    }

    public function removeSecondaryIndustry(SecondaryIndustry $secondaryIndustry
    ): self {
        if ($this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries->removeElement($secondaryIndustry);
        }

        return $this;
    }

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @return string
     * @throws \ReflectionException
     */
    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getStartDateAndTimeTimeStamp()
    {
        if ($this->startDateAndTime) {
            return $this->startDateAndTime->getTimestamp();
        }

        return '';
    }

    /**
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getEndDateAndTimeTimeStamp()
    {
        if ($this->endDateAndTime) {
            return $this->endDateAndTime->getTimestamp();
        }

        return '';
    }

    /**
     * @return Collection|Registration[]
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): self
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations[] = $registration;
            $registration->setExperience($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->registrations->contains($registration)) {
            $this->registrations->removeElement($registration);
            // set the owning side to null (unless already changed)
            if ($registration->getExperience() === $this) {
                $registration->setExperience(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Feedback[]
     */
    public function getFeedback(): Collection
    {
        return $this->feedback;
    }

    public function addFeedback(Feedback $feedback): self
    {
        if (!$this->feedback->contains($feedback)) {
            $this->feedback[] = $feedback;
            $feedback->setExperience($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): self
    {
        if ($this->feedback->contains($feedback)) {
            $this->feedback->removeElement($feedback);
            // set the owning side to null (unless already changed)
            if ($feedback->getExperience() === $this) {
                $feedback->setExperience(null);
            }
        }

        return $this;
    }

    public function isRegistered(User $user)
    {
        foreach ($this->getRegistrations() as $registration) {
            if ($registration->getUser()->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getRegistrationForUser(User $user)
    {
        foreach ($this->getRegistrations() as $registration) {
            if ($registration->getUser()->getId() === $user->getId()) {
                return $registration;
            }
        }

        return false;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @return string
     */
    public function getFormattedAddress()
    {
        return sprintf(
            "%s %s %s %s",
            $this->street,
            $this->city,
            $this->state ? $this->state->getAbbreviation() : '',
            $this->zipcode
        );
    }

    public function getCancelled(): ?bool
    {
        return $this->cancelled;
    }

    public function setCancelled(?bool $cancelled): self
    {
        $this->cancelled = $cancelled;

        return $this;
    }


    /**
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getFriendlyStartDateAndTime()
    {
        if ($this->startDateAndTime) {
            return $this->startDateAndTime->format("m/d/Y h:i A");
        }

        return '';
    }

    /**
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getFriendlyEndDateAndTime()
    {
        if ($this->endDateAndTime) {
            return $this->endDateAndTime->format("m/d/Y h:i A");
        }

        return '';
    }

    /**
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getFriendlyEventName()
    {
        if ($this->getType()) {
            return $this->getType()->getEventName();
        }

        return '';
    }

    /**
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getExperienceListTitle()
    {
        return $this->getTitle();
    }

    public function getRequireApproval(): ?bool
    {
        return $this->requireApproval;
    }

    public function setRequireApproval(?bool $requireApproval): self
    {
        $this->requireApproval = $requireApproval;

        return $this;
    }

    /**
     * @return Collection|Share[]
     */
    public function getShares(): Collection
    {
        return $this->shares;
    }

    public function addShare(Share $share): self
    {
        if (!$this->shares->contains($share)) {
            $this->shares[] = $share;
            $share->setExperience($this);
        }

        return $this;
    }

    public function removeShare(Share $share): self
    {
        if ($this->shares->removeElement($share)) {
            // set the owning side to null (unless already changed)
            if ($share->getExperience() === $this) {
                $share->setExperience(null);
            }
        }

        return $this;
    }

    public function getNpsScore(): ?int
    {
        return $this->npsScore;
    }

    public function setNpsScore(?int $npsScore): self
    {
        $this->npsScore = $npsScore;

        return $this;
    }

    public function getTotalResponses(): ?int
    {
        return $this->totalResponses;
    }

    public function setTotalResponses(?int $totalResponses): self
    {
        $this->totalResponses = $totalResponses;

        return $this;
    }

    public function getAverageRating(): ?int
    {
        return $this->averageRating;
    }

    public function setAverageRating(?int $averageRating): self
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getOriginalRequest()
    {
        return $this->request;
    }

    public function getAddressSearch(): ?string
    {
        return $this->addressSearch;
    }

    public function setAddressSearch(?string $addressSearch): self
    {
        $this->addressSearch = $addressSearch;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getUtcStartDateAndTime(): ?\DateTimeInterface
    {
        return $this->utcStartDateAndTime;
    }

    public function setUtcStartDateAndTime(
        ?\DateTimeInterface $utcStartDateAndTime
    ): self {
        $this->utcStartDateAndTime = $utcStartDateAndTime;

        return $this;
    }

    public function getUtcEndDateAndTime(): ?\DateTimeInterface
    {
        return $this->utcEndDateAndTime;
    }

    public function setUtcEndDateAndTime(?\DateTimeInterface $utcEndDateAndTime
    ): self {
        $this->utcEndDateAndTime = $utcEndDateAndTime;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection|ExperienceResource[]
     */
    public function getExperienceResources(): Collection
    {
        return $this->experienceResources;
    }

    public function addExperienceResource(ExperienceResource $experienceResource
    ): self {
        if (!$this->experienceResources->contains($experienceResource)) {
            $this->experienceResources[] = $experienceResource;
            $experienceResource->setExperience($this);
        }

        return $this;
    }

    public function removeExperienceResource(
        ExperienceResource $experienceResource
    ): self {
        if ($this->experienceResources->removeElement($experienceResource)) {
            // set the owning side to null (unless already changed)
            if ($experienceResource->getExperience() === $this) {
                $experienceResource->setExperience(null);
            }
        }

        return $this;
    }

    public function isSchoolExperience()
    {
        return $this instanceof SchoolExperience;
    }

    public function isCompanyExperience()
    {
        return $this instanceof CompanyExperience;
    }

    public function getCoordinator()
    {
        if ($this instanceof SchoolExperience) {
            return $this->getSchoolContact();
        }

        if ($this instanceof CompanyExperience) {
            return $this->getEmployeeContact();
        }

        return null;
    }

    public function getMapMarkerIcon()
    {
        if ($this instanceof SchoolExperience) {
            return 'school';
        }

        if ($this instanceof CompanyExperience) {
            return 'company';
        }

        return 'company';
    }

    public function getIsRecurring(): ?bool
    {
        return $this->isRecurring;
    }

    public function setIsRecurring(bool $isRecurring): self
    {
        $this->isRecurring = $isRecurring;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param  mixed  $startDate
     */
    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param  mixed  $startTime
     */
    public function setStartTime($startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getRecurrenceRule(): ?string
    {
        return $this->recurrenceRule;
    }

    public function setRecurrenceRule(?string $recurrenceRule): self
    {
        $this->recurrenceRule = $recurrenceRule;

        return $this;
    }

    public function getSchedule(): ?array
    {
        if (!$this->schedule) {
            return [];
        }

        return $this->schedule;
    }

    public function setSchedule(?array $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getParentEvent(): ?self
    {
        return $this->parentEvent;
    }

    public function setParentEvent(?self $parentEvent): self
    {
        $this->parentEvent = $parentEvent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildEvents(): Collection
    {
        return $this->childEvents;
    }

    public function addChildEvent(self $childEvent): self
    {
        if (!$this->childEvents->contains($childEvent)) {
            $this->childEvents[] = $childEvent;
            $childEvent->setParentEvent($this);
        }

        return $this;
    }

    public function removeChildEvent(self $childEvent): self
    {
        if ($this->childEvents->removeElement($childEvent)) {
            // set the owning side to null (unless already changed)
            if ($childEvent->getParentEvent() === $this) {
                $childEvent->setParentEvent(null);
            }
        }

        return $this;
    }
}
