<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\Criteria;

/**
 * @CustomAssert\ProfessionalAlreadyOwnsCompany(groups={"CREATE"})
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Company
{
    use Timestampable;

    /**
     *
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "VIDEO"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please enter your phone number in xxx-xxx-xxxx format.", groups={"CREATE", "EDIT"})
     * @Assert\Regex(
     *     pattern="/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/",
     *     match=true,
     *     message="The phone number needs to be in this format: xxx-xxx-xxxx",
     *     groups={"CREATE", "EDIT"}
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneExt;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyLinkedinPage;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyFacebookPage;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyInstagramPage;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyTwitterPage;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a primary contact!")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $primaryContact;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProfessionalUser", mappedBy="company")
     */
    private $professionalUsers;

    /**
     * @ORM\OneToMany(targetEntity="CompanyPhoto", mappedBy="company", orphanRemoval=true, cascade={"persist"})
     */
    private $companyPhotos;

    /**
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "VIDEO"})
     * @Assert\NotBlank(message="Please enter your company name.", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Please enter a short description.", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $shortDescription;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Please enter a long description.", groups={"EDIT"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Please enter your website.", groups={"CREATE", "EDIT"})
     * @Assert\Regex("/^(http|https):\/\//", message="Website must start with http or https!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $website;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Please enter your email address.", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $emailAddress;

    /**
     * @Groups({"RESULTS_PAGE", "VIDEO"})
     * @Assert\NotBlank(message="Please choose at least one primary industry.", groups={"EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Industry", inversedBy="companies")
     */
    private $primaryIndustry;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $thumbnailImage;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $featuredImage;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyResource", mappedBy="company", cascade={"remove"}, orphanRemoval=true)
     */
    private $companyResources;

    /**
     * @Assert\NotBlank(message="Don't forget an owner!", groups={"EDIT"})
     * @ORM\OneToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="ownedCompany")
     * @JoinColumn(onDelete="SET NULL")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyFavorite", mappedBy="company", orphanRemoval=true)
     */
    private $companyFavorites;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     * @var boolean
     */
    private $isFavorite;

    /**
     * @var boolean
     */
    private $isMine;

    /**
     * @Groups({"RESULTS_PAGE", "VIDEO"})
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please choose at least one career field.",
     *     groups={"SECONDARY_INDUSTRY"}
     * )
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="companies")
     */
    private $secondaryIndustries;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please specify at least one school.",
     *     groups={"EDIT", "CREATE"}
     * )
     * @ORM\ManyToMany(targetEntity="App\Entity\School", inversedBy="companies")
     */
    private $schools;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyVideo", mappedBy="company", orphanRemoval=true)
     */
    private $companyVideos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyExperience", mappedBy="company", orphanRemoval=true)
     */
    private $companyExperiences;

    /**
     * @ORM\Column(type="boolean")
     */
    private $approved = false;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\StudentUser", mappedBy="companiesInterestedIn")
     */
    private $studentUsers;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
     */
    private $latitude;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
     */
    private $longitude;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please enter your street address.", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please enter your city.", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please enter your zipcode.", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zipcode;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Please choose your state.", groups={"CREATE", "EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\State")
     */
    private $state;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

	/**
	 * @var string
	 */
    private $geoRadius;

	/**
	 * @var string
	 */
    private $geoZipCode;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select at least one region",
     *      groups={"EDIT", "CREATE"}
     * )
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Region", inversedBy="companies")
     */
    private $regions;

    /**
     * @ORM\OneToMany(targetEntity=CompanyView::class, mappedBy="company", orphanRemoval=true)
     */
    private $companyViews;

    public function __construct()
    {
        $this->professionalUsers = new ArrayCollection();
        $this->companyPhotos = new ArrayCollection();
        $this->companyResources = new ArrayCollection();
        $this->companyFavorites = new ArrayCollection();
        $this->secondaryIndustries = new ArrayCollection();
        $this->schools = new ArrayCollection();
        $this->companyVideos = new ArrayCollection();
        $this->companyExperiences = new ArrayCollection();
        $this->studentUsers = new ArrayCollection();
        $this->regions = new ArrayCollection();
        $this->companyViews = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone(?string $phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhoneExt()
    {
        return $this->phoneExt;
    }

    public function setPhoneExt(?string $phoneExt)
    {
        $this->phoneExt = $phoneExt;

        return $this;
    }

    public function getCompanyLinkedinPage()
    {
        return $this->companyLinkedinPage;
    }

    public function setCompanyLinkedinPage(?string $companyLinkedinPage)
    {
        $this->companyLinkedinPage = $companyLinkedinPage;

        return $this;
    }

    public function getCompanyFacebookPage()
    {
        return $this->companyFacebookPage;
    }

    public function setCompanyFacebookPage(?string $companyFacebookPage)
    {
        $this->companyFacebookPage = $companyFacebookPage;

        return $this;
    }

    public function getCompanyInstagramPage()
    {
        return $this->companyInstagramPage;
    }

    public function setCompanyInstagramPage(?string $companyInstagramPage)
    {
        $this->companyInstagramPage = $companyInstagramPage;

        return $this;
    }

    public function getCompanyTwitterPage()
    {
        return $this->companyTwitterPage;
    }

    public function setCompanyTwitterPage(?string $companyTwitterPage)
    {
        $this->companyTwitterPage = $companyTwitterPage;

        return $this;
    }

    public function getPrimaryContact()
    {
        return $this->primaryContact;
    }

    public function setPrimaryContact(?string $primaryContact)
    {
        $this->primaryContact = $primaryContact;

        return $this;
    }

    /**
     * @return Collection|ProfessionalUser[]
     */
    public function getProfessionalUsers()
    {
        return $this->professionalUsers;
    }

    public function addProfessionalUser(ProfessionalUser $professionalUser)
    {
        if (!$this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers[] = $professionalUser;
            $professionalUser->setCompany($this);
        }

        return $this;
    }

    public function removeProfessionalUser(ProfessionalUser $professionalUser)
    {
        if ($this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers->removeElement($professionalUser);
            // set the owning side to null (unless already changed)
            if ($professionalUser->getCompany() === $this) {
                $professionalUser->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @return string
     */
    public function getFeaturedImagePath()
    {
        if($this->getFeaturedImage()) {
            return UploaderHelper::FEATURE_IMAGE.'/'.$this->getFeaturedImage()->getFileName();
        }
        return '';
    }

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @return string
     */
    public function getThumbnailImagePath()
    {
        if($this->getThumbnailImage()) {
            return UploaderHelper::THUMBNAIL_IMAGE.'/'.$this->getThumbnailImage()->getFileName();
        }
        return '';
    }

    /**
     * @return Collection|CompanyPhoto[]
     */
    public function getCompanyPhotos()
    {
        return $this->companyPhotos;
    }

    public function addCompanyPhoto(CompanyPhoto $companyPhoto)
    {
        if (!$this->companyPhotos->contains($companyPhoto)) {
            $this->companyPhotos[] = $companyPhoto;
            $companyPhoto->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyPhoto(CompanyPhoto $companyPhoto)
    {
        if ($this->companyPhotos->contains($companyPhoto)) {
            $this->companyPhotos->removeElement($companyPhoto);
            // set the owning side to null (unless already changed)
            if ($companyPhoto->getCompany() === $this) {
                $companyPhoto->setCompany(null);
            }
        }

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;

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

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getPrimaryIndustry()
    {
        return $this->primaryIndustry;
    }

    public function setPrimaryIndustry(?Industry $primaryIndustry)
    {
        $this->primaryIndustry = $primaryIndustry;

        return $this;
    }

    public function getThumbnailImage()
    {
        return $this->thumbnailImage;
    }

    public function setThumbnailImage(?Image $thumbnailImage)
    {
        $this->thumbnailImage = $thumbnailImage;

        return $this;
    }

    public function getFeaturedImage()
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?Image $featuredImage)
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }

    /**
     * @return Collection|CompanyResource[]
     */
    public function getCompanyResources()
    {
        return $this->companyResources;
    }

    public function addCompanyResource(CompanyResource $companyResource)
    {
        if (!$this->companyResources->contains($companyResource)) {
            $this->companyResources[] = $companyResource;
            $companyResource->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyResource(CompanyResource $companyResource)
    {
        if ($this->companyResources->contains($companyResource)) {
            $this->companyResources->removeElement($companyResource);
            // set the owning side to null (unless already changed)
            if ($companyResource->getCompany() === $this) {
                $companyResource->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA"})
     */
    public function getThumbnailImageURL() {
        if($this->getThumbnailImage()) {
            return '/media/cache/squared_thumbnail_small/uploads/' . $this->getThumbnailImagePath();
        }
        return '';
    }

    /**
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA"})
     */
    public function getFeaturedImageURL() {
        if($this->getFeaturedImage()) {
            return '/uploads/' . $this->getFeaturedImagePath();
        }
        return '';
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(?ProfessionalUser $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|CompanyFavorite[]
     */
    public function getCompanyFavorites(): Collection
    {
        return $this->companyFavorites;
    }

    public function addCompanyFavorite(CompanyFavorite $companyFavorite): self
    {
        if (!$this->companyFavorites->contains($companyFavorite)) {
            $this->companyFavorites[] = $companyFavorite;
            $companyFavorite->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyFavorite(CompanyFavorite $companyFavorite): self
    {
        if ($this->companyFavorites->contains($companyFavorite)) {
            $this->companyFavorites->removeElement($companyFavorite);
            // set the owning side to null (unless already changed)
            if ($companyFavorite->getCompany() === $this) {
                $companyFavorite->setCompany(null);
            }
        }

        return $this;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted($deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @Groups({"RESULTS_PAGE"})
     * @return bool
     */
    public function isFavorite()
    {
        return $this->isFavorite;
    }

    /**
     * @param bool $isFavorite
     */
    public function setIsFavorite($isFavorite)
    {
        $this->isFavorite = $isFavorite;
    }

    /**
     * @Groups({"RESULTS_PAGE"})
     * @return bool
     */
    public function isMine()
    {
        return $this->isMine;
    }

    /**
     * @param bool $isMine
     */
    public function setIsMine($isMine)
    {
        $this->isMine = $isMine;
    }

    /**
     * @return Collection|SecondaryIndustry[]
     */
    public function getSecondaryIndustries(): Collection
    {
        return $this->secondaryIndustries;
    }

    public function addSecondaryIndustry(SecondaryIndustry $secondaryIndustry)
    {
        if (!$this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries[] = $secondaryIndustry;
        }

        return $this;
    }

    public function removeSecondaryIndustry(SecondaryIndustry $secondaryIndustry)
    {
        if ($this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries->removeElement($secondaryIndustry);
        }

        return $this;
    }

    /**
     * @return Collection|School[]
     */
    public function getSchools(): Collection
    {
        return $this->schools;
    }

    public function addSchool(School $school): self
    {
        if (!$this->schools->contains($school)) {
            $this->schools[] = $school;
        }

        return $this;
    }

    public function removeSchool(School $school): self
    {
        if ($this->schools->contains($school)) {
            $this->schools->removeElement($school);
        }

        return $this;
    }

    /**
     * @return Collection|CompanyVideo[]
     */
    public function getCompanyVideos(): Collection
    {
        return $this->companyVideos;
    }

    public function addCompanyVideo(CompanyVideo $companyVideo): self
    {
        if (!$this->companyVideos->contains($companyVideo)) {
            $this->companyVideos[] = $companyVideo;
            $companyVideo->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyVideo(CompanyVideo $companyVideo): self
    {
        if ($this->companyVideos->contains($companyVideo)) {
            $this->companyVideos->removeElement($companyVideo);
            // set the owning side to null (unless already changed)
            if ($companyVideo->getCompany() === $this) {
                $companyVideo->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CompanyExperience[]
     */
    public function getCompanyExperiences(): Collection
    {
        return $this->companyExperiences;
    }

    public function addCompanyExperience(CompanyExperience $companyExperience): self
    {
        if (!$this->companyExperiences->contains($companyExperience)) {
            $this->companyExperiences[] = $companyExperience;
            $companyExperience->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyExperience(CompanyExperience $companyExperience): self
    {
        if ($this->companyExperiences->contains($companyExperience)) {
            $this->companyExperiences->removeElement($companyExperience);
            // set the owning side to null (unless already changed)
            if ($companyExperience->getCompany() === $this) {
                $companyExperience->setCompany(null);
            }
        }

        return $this;
    }

    public function getActiveCompanyExperiences(): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('cancelled', false));

        return $this->companyExperiences->matching($criteria);
    }

    public function isFavoritedByUser(User $user)
    {
        return ($this->companyFavorites->filter(
                function (CompanyFavorite $companyFavorite) use ($user) {
                    return $companyFavorite->getUser()->getId() === $user->getId();
                }
            )->count() > 0);
    }

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
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
            $studentUser->addCompaniesInterestedIn($this);
        }

        return $this;
    }

    public function removeStudentUser(StudentUser $studentUser): self
    {
        if ($this->studentUsers->contains($studentUser)) {
            $this->studentUsers->removeElement($studentUser);
            $studentUser->removeCompaniesInterestedIn($this);
        }

        return $this;
    }

    public function isUserOwner(User $user) {
        if($user->getId() === $this->getOwner()->getId()) {
            return true;
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

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

    public function getAddress() {
        return $this->getFormattedAddress();
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getFormattedAddress() {
        return sprintf("%s %s %s %s",
            $this->street,
            $this->city,
            $this->state ? $this->state->getAbbreviation() : '',
            $this->zipcode
        );
    }

	/**
	 * @return string
	 */
	public function getGeoRadius(): ?string {
                                             		return $this->geoRadius;
                                             	}

	/**
	 * @param string $geoRadius
	 */
	public function setGeoRadius( ?string $geoRadius ): void {
                                             		$this->geoRadius = $geoRadius;
                                             	}

	/**
	 * @return string
	 */
	public function getGeoZipCode(): ?string {
                                             		return $this->geoZipCode;
                                             	}

	/**
	 * @param string $geoZipCode
	 */
	public function setGeoZipCode( ?string $geoZipCode ): void {
                                             		$this->geoZipCode = $geoZipCode;
                                             	}

    /**
     * @return Collection|Region[]
     */
    public function getRegions(): Collection
    {
        return $this->regions;
    }

    public function addRegion(Region $region): self
    {
        if (!$this->regions->contains($region)) {
            $this->regions[] = $region;
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        if ($this->regions->contains($region)) {
            $this->regions->removeElement($region);
        }

        return $this;
    }

    /**
     * @return Collection|CompanyView[]
     */
    public function getCompanyViews(): Collection
    {
        return $this->companyViews;
    }

    public function addCompanyView(CompanyView $companyView): self
    {
        if (!$this->companyViews->contains($companyView)) {
            $this->companyViews[] = $companyView;
            $companyView->setCompanyId($this);
        }

        return $this;
    }

    public function removeCompanyView(CompanyView $companyView): self
    {
        if ($this->companyViews->removeElement($companyView)) {
            // set the owning side to null (unless already changed)
            if ($companyView->getCompanyId() === $this) {
                $companyView->setCompanyId(null);
            }
        }

        return $this;
    }
}
