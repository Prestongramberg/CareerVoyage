<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceRepository")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"companyExperience" = "CompanyExperience", "schoolExperience" = "SchoolExperience"})
 */
abstract class Experience
{
    public static $types = [
        'Site Visit' => 'SITE_VISIT',
        'Event' => 'EVENT',
        'Externship' => 'EXTERNSHIP',
        'Internship' => 'INTERNSHIP',
        'Job' => 'JOB',
    ];

    public static $paymentTypes = [
        'Per Person And Per Visit' => 'PER_PERSON_AND_PER_VISIT',
        'Hour' => 'HOUR',
        'Day' => 'DAY',
        'Week' => 'WEEK',
        'Month' => 'MONTH',
        'Year' => 'YEAR',
    ];

    /**
     * @Assert\Callback(groups={"CREATE"})
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if(!$this->payment) {
            $context->buildViolation('You must enter a payment!')
                ->atPath('payment')
                ->addViolation();
            return;
        }

        if(!is_float($this->payment) && !is_numeric($this->payment)) {
            $context->buildViolation('You must enter a valid number or decimal for the payment!')
                ->atPath('payment')
                ->addViolation();
        }
    }

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a title!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a brief description!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    protected $briefDescription;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    protected $about;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Career", inversedBy="experiences")
     */
    protected $careers;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $availableSpaces;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $payment;

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
     * @Assert\NotBlank(message="Don't forget a street!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    protected $street;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @Assert\NotBlank(message="Don't forget a city!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    protected $city;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @Assert\NotBlank(message="Don't forget a zipcode!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    protected $zipcode;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @Assert\NotBlank(message="Don't forget a start date", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startDateAndTime;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @Assert\NotBlank(message="Don't forget an end date", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDateAndTime;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     groups={"CREATE", "EDIT"}
     * )
     * @Assert\NotBlank(message="Don't forget an email!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\OneToMany(targetEntity="App\Entity\ExperienceFile", mappedBy="experience", cascade={"remove"}, orphanRemoval=true)
     */
    protected $experienceFiles;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @Assert\NotBlank(message="Don't forget a state!", groups={"CREATE", "EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="experiences")
     */
    protected $state;

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget to select a type!", groups={"CREATE", "EDIT"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RolesWillingToFulfill", inversedBy="experiences")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $type;

    public function __construct()
    {
        $this->careers = new ArrayCollection();
        $this->experienceFiles = new ArrayCollection();
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
    
    /**
     * @return Collection|Career[]
     */
    public function getCareers(): Collection
    {
        return $this->careers;
    }

    public function addCareer(Career $career)
    {
        if (!$this->careers->contains($career)) {
            $this->careers[] = $career;
        }

        return $this;
    }

    public function removeCareer(Career $career)
    {
        if ($this->careers->contains($career)) {
            $this->careers->removeElement($career);
        }

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

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet(string $street)
    {
        $this->street = $street;

        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity(string $city)
    {
        $this->city = $city;

        return $this;
    }
    
    public function getZipcode()
    {
        return $this->zipcode;
    }

    public function setZipcode($zipcode)
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
}
