<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceRepository")
 */
class Experience
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget a title!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Assert\NotBlank(message="Don't forget a brief description!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $briefDescription;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $about;

    /**
     * @Assert\NotBlank(message="Don't forget to select a type!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Career", inversedBy="experiences")
     */
    private $careers;

    /**
     * @ORM\Column(type="integer")
     */
    private $availableSpaces;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $payment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentShownIsPer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @Assert\NotBlank(message="Don't forget a street!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @Assert\NotBlank(message="Don't forget a city!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @Assert\NotBlank(message="Don't forget a state!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $state;

    /**
     * @Assert\NotBlank(message="Don't forget a zipcode!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $zipcode;

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDateAndTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDateAndTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $length;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="experience", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $employeeContact;

    /**
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     groups={"CREATE"}
     * )
     * @Assert\NotBlank(message="Don't forget an email!", groups={"CREATE"})
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExperienceWaver", mappedBy="experience", orphanRemoval=true)
     */
    private $experienceWavers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExperienceFile", mappedBy="experience", orphanRemoval=true)
     */
    private $experienceFiles;

    public function __construct()
    {
        $this->careers = new ArrayCollection();
        $this->experienceWavers = new ArrayCollection();
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

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

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

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;

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

    public function getLength()
    {
        return $this->length;
    }

    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    public function getEmployeeContact()
    {
        return $this->employeeContact;
    }

    public function setEmployeeContact(ProfessionalUser $employeeContact)
    {
        $this->employeeContact = $employeeContact;

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
     * @return Collection|ExperienceWaver[]
     */
    public function getExperienceWavers(): Collection
    {
        return $this->experienceWavers;
    }

    public function addExperienceWaver(ExperienceWaver $experienceWaver): self
    {
        if (!$this->experienceWavers->contains($experienceWaver)) {
            $this->experienceWavers[] = $experienceWaver;
            $experienceWaver->setExperience($this);
        }

        return $this;
    }

    public function removeExperienceWaver(ExperienceWaver $experienceWaver): self
    {
        if ($this->experienceWavers->contains($experienceWaver)) {
            $this->experienceWavers->removeElement($experienceWaver);
            // set the owning side to null (unless already changed)
            if ($experienceWaver->getExperience() === $this) {
                $experienceWaver->setExperience(null);
            }
        }

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
}
