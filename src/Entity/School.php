<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class School
{
    use Timestampable;

    const COMMUNICATION_TYPE_DEFAULT = 'COMMUNICATION_TYPE_DEFAULT';
    const COMMUNICATION_TYPE_TEACHER_APPROVAL_NOT_REQUIRED = 'COMMUNICATION_TYPE_TEACHER_APPROVAL_NOT_REQUIRED';
    const COMMUNICATION_TYPE_TEACHER_APPROVAL_REQUIRED = 'COMMUNICATION_TYPE_TEACHER_APPROVAL_REQUIRED';

    public static $communicationTypes = [
        'Default' => self::COMMUNICATION_TYPE_DEFAULT,
        'Teacher Approval Not Required' => self::COMMUNICATION_TYPE_TEACHER_APPROVAL_NOT_REQUIRED,
        'Teacher Approval Required' => self::COMMUNICATION_TYPE_TEACHER_APPROVAL_REQUIRED,
    ];

    /**
     * @Groups({"ALL_USER_DATA", "RESULTS_PAGE", "EDUCATOR_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"ALL_USER_DATA", "RESULTS_PAGE", "EDUCATOR_USER_DATA", "SCHOOL_ADMINISTRATOR"})
     * @Assert\NotBlank(message="Don't forget a name!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", mappedBy="schools")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $companies;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ProfessionalUser", mappedBy="schools")
     */
    private $professionalUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorUser", mappedBy="school")
     * @ORM\OrderBy({"lastName" = "ASC"})
     */
    private $educatorUsers;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a school email!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @Assert\NotBlank(message="Don't forget to add an overview and background!", groups={"EDIT"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $overviewAndBackground;

    /**
     * @Groups({"ALL_USER_DATA", "RESULTS_PAGE"})
     * @ORM\ManyToMany(targetEntity="App\Entity\SchoolAdministrator", inversedBy="schools")
     */
    private $schoolAdministrators;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="schools")
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolVideo", mappedBy="school")
     */
    private $schoolVideos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolExperience", mappedBy="school")
     */
    private $schoolExperiences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolPhoto", mappedBy="school")
     */
    private $schoolPhotos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentUser", mappedBy="school")
     * @OrderBy({"lastName" = "ASC"})
     */
    private $studentUsers;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a street!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a city!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a zipcode!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zipcode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="schools")
     */
    private $site;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a state!", groups={"EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="schools")
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeachLessonExperience", mappedBy="school")
     */
    private $teachLessonExperiences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolResource", mappedBy="school")
     */
    private $schoolResources;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     */
    private $latitude;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist"})
     */
    private $thumbnailImage;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist"})
     */
    private $featuredImage;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "The short description cannot be longer than {{ limit }} characters",
     *      groups={"EDIT"}
     * )
     * @Assert\NotBlank(message="Don't forget a short description!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shortDescription;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a phone number!", groups={"EDIT"})
     * @Assert\Regex(
     *     pattern="/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/",
     *     match=true,
     *     message="The phone number needs to be in this format: xxx-xxx-xxxx",
     *     groups={"EDIT"}
     * )
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a website!", groups={"EDIT"})
     * @Assert\Regex("/^(http|https):\/\//", message="Website must start with http or https!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolLinkedInPage;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolFacebookPage;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolInstagramPage;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolTwitterPage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $communicationType = self::COMMUNICATION_TYPE_DEFAULT;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private $allowEventCreation = false;

    /**
     * @ORM\ManyToMany(targetEntity=ReportShare::class, mappedBy="schools")
     */
    private $reportShares;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->professionalUsers = new ArrayCollection();
        $this->educatorUsers = new ArrayCollection();
        $this->schoolAdministrators = new ArrayCollection();
        $this->schoolVideos = new ArrayCollection();
        $this->schoolExperiences = new ArrayCollection();
        $this->schoolPhotos = new ArrayCollection();
        $this->studentUsers = new ArrayCollection();
        $this->teachLessonRequests = new ArrayCollection();
        $this->teachLessonExperiences = new ArrayCollection();
        $this->schoolResources = new ArrayCollection();
        $this->reportShares = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->addSchool($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            $company->removeSchool($this);
        }

        return $this;
    }

    /**
     * @return Collection|ProfessionalUser[]
     */
    public function getProfessionalUsers(): Collection
    {
        return $this->professionalUsers;
    }

    public function addProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if (!$this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers[] = $professionalUser;
            $professionalUser->addSchool($this);
        }

        return $this;
    }

    public function removeProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if ($this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers->removeElement($professionalUser);
            $professionalUser->removeSchool($this);
        }

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
            $educatorUser->setSchool($this);
        }

        return $this;
    }

    public function removeEducatorUser(EducatorUser $educatorUser): self
    {
        if ($this->educatorUsers->contains($educatorUser)) {
            $this->educatorUsers->removeElement($educatorUser);
            // set the owning side to null (unless already changed)
            if ($educatorUser->getSchool() === $this) {
                $educatorUser->setSchool(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOverviewAndBackground(): ?string
    {
        return $this->overviewAndBackground;
    }

    public function setOverviewAndBackground(?string $overviewAndBackground): self
    {
        $this->overviewAndBackground = $overviewAndBackground;

        return $this;
    }

    public function isUserSchoolAdministrator(User $user) {

        return ($this->getSchoolAdministrators()->filter(function(SchoolAdministrator $schoolAdministrator) use ($user) {
            return $schoolAdministrator->getId() === $user->getId();
        })->count() > 0);
    }

    /**
     * @return Collection|SchoolAdministrator[]
     */
    public function getSchoolAdministrators(): Collection
    {
        return $this->schoolAdministrators;
    }

    public function addSchoolAdministrator(SchoolAdministrator $schoolAdministrator): self
    {
        if (!$this->schoolAdministrators->contains($schoolAdministrator)) {
            $this->schoolAdministrators[] = $schoolAdministrator;
        }

        return $this;
    }

    public function removeSchoolAdministrator(SchoolAdministrator $schoolAdministrator): self
    {
        if ($this->schoolAdministrators->contains($schoolAdministrator)) {
            $this->schoolAdministrators->removeElement($schoolAdministrator);
        }

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection|SchoolVideo[]
     */
    public function getSchoolVideos(): Collection
    {
        return $this->schoolVideos;
    }

    public function addSchoolVideo(SchoolVideo $schoolVideo): self
    {
        if (!$this->schoolVideos->contains($schoolVideo)) {
            $this->schoolVideos[] = $schoolVideo;
            $schoolVideo->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolVideo(SchoolVideo $schoolVideo): self
    {
        if ($this->schoolVideos->contains($schoolVideo)) {
            $this->schoolVideos->removeElement($schoolVideo);
            // set the owning side to null (unless already changed)
            if ($schoolVideo->getSchool() === $this) {
                $schoolVideo->setSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SchoolExperience[]
     */
    public function getSchoolExperiences(): Collection
    {
        return $this->schoolExperiences;
    }

    public function getSortedSchoolExperiences(): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('cancelled', false))
                 ->orderBy(array ('startDateAndTime' => Criteria::DESC));

        return $this->schoolExperiences->matching($criteria);
    }

    public function addSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if (!$this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences[] = $schoolExperience;
            $schoolExperience->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if ($this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences->removeElement($schoolExperience);
            // set the owning side to null (unless already changed)
            if ($schoolExperience->getSchool() === $this) {
                $schoolExperience->setSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SchoolPhoto[]
     */
    public function getSchoolPhotos(): Collection
    {
        return $this->schoolPhotos;
    }

    public function addSchoolPhoto(SchoolPhoto $schoolPhoto): self
    {
        if (!$this->schoolPhotos->contains($schoolPhoto)) {
            $this->schoolPhotos[] = $schoolPhoto;
            $schoolPhoto->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolPhoto(SchoolPhoto $schoolPhoto): self
    {
        if ($this->schoolPhotos->contains($schoolPhoto)) {
            $this->schoolPhotos->removeElement($schoolPhoto);
            // set the owning side to null (unless already changed)
            if ($schoolPhoto->getSchool() === $this) {
                $schoolPhoto->setSchool(null);
            }
        }

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
            $studentUser->setSchool($this);
        }

        return $this;
    }

    public function removeStudentUser(StudentUser $studentUser): self
    {
        if ($this->studentUsers->contains($studentUser)) {
            $this->studentUsers->removeElement($studentUser);
            // set the owning side to null (unless already changed)
            if ($studentUser->getSchool() === $this) {
                $studentUser->setSchool(null);
            }
        }

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

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

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

    /**
     * @return Collection|TeachLessonExperience[]
     */
    public function getTeachLessonExperiences(): Collection
    {
        return $this->teachLessonExperiences;
    }

    public function addTeachLessonExperience(TeachLessonExperience $teachLessonExperience): self
    {
        if (!$this->teachLessonExperiences->contains($teachLessonExperience)) {
            $this->teachLessonExperiences[] = $teachLessonExperience;
            $teachLessonExperience->setSchool($this);
        }

        return $this;
    }

    public function removeTeachLessonExperience(TeachLessonExperience $teachLessonExperience): self
    {
        if ($this->teachLessonExperiences->contains($teachLessonExperience)) {
            $this->teachLessonExperiences->removeElement($teachLessonExperience);
            // set the owning side to null (unless already changed)
            if ($teachLessonExperience->getSchool() === $this) {
                $teachLessonExperience->setSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SchoolResource[]
     */
    public function getSchoolResources(): Collection
    {
        return $this->schoolResources;
    }

    public function addSchoolResource(SchoolResource $schoolResource): self
    {
        if (!$this->schoolResources->contains($schoolResource)) {
            $this->schoolResources[] = $schoolResource;
            $schoolResource->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolResource(SchoolResource $schoolResource): self
    {
        if ($this->schoolResources->contains($schoolResource)) {
            $this->schoolResources->removeElement($schoolResource);
            // set the owning side to null (unless already changed)
            if ($schoolResource->getSchool() === $this) {
                $schoolResource->setSchool(null);
            }
        }

        return $this;
    }

	/**
	 * @Groups({"ALL_USER_DATA", "RESULTS_PAGE"})
	 * @return string
	 */
    public function getAddress() {
        return sprintf("%s %s %s %s",
            $this->street,
            $this->city,
	        $this->state ? $this->state->getAbbreviation() : '',
            $this->zipcode
        );
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

    public function getFeaturedImage()
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?Image $featuredImage)
    {
        $this->featuredImage = $featuredImage;

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

    /**
     * @Groups({"RESULTS_PAGE"})
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
     * @Groups({"RESULTS_PAGE"})
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
     * @Groups({"RESULTS_PAGE"})
     */
    public function getThumbnailImageURL() {
        if($this->getThumbnailImage()) {
            return '/media/cache/squared_thumbnail_small/uploads/' . $this->getThumbnailImagePath();
        }
        return '';
    }

    /**
     * @Groups({"RESULTS_PAGE"})
     */
    public function getFeaturedImageURL() {
        if($this->getFeaturedImage()) {
            return '/uploads/' . $this->getFeaturedImagePath();
        }
        return '';
    }

    /**
     * Just allowing school administrators to create experiences
     * @param User $user
     * @return bool
     */
    public function canCreateExperiences(User $user)
    {
        return ($this->schoolAdministrators->filter(
                function (SchoolAdministrator $schoolAdministrator) use ($user) {
                    return $schoolAdministrator->getId() === $user->getId();
                }
            )->count() > 0);
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getSchoolLinkedInPage(): ?string
    {
        return $this->schoolLinkedInPage;
    }

    public function setSchoolLinkedInPage(?string $schoolLinkedInPage): self
    {
        $this->schoolLinkedInPage = $schoolLinkedInPage;

        return $this;
    }

    public function getCommunicationType(): ?string
    {
        return $this->communicationType;
    }

    public function setCommunicationType(?string $communicationType): self
    {
        $this->communicationType = $communicationType;

        return $this;
    }

    public function getSchoolFacebookPage()
    {
        return $this->schoolFacebookPage;
    }

    public function setSchoolFacebookPage(?string $schoolFacebookPage)
    {
        $this->schoolFacebookPage = $schoolFacebookPage;

        return $this;
    }

    public function getSchoolInstagramPage()
    {
        return $this->schoolInstagramPage;
    }

    public function setSchoolInstagramPage(?string $schoolInstagramPage)
    {
        $this->schoolInstagramPage = $schoolInstagramPage;

        return $this;
    }

    public function getSchoolTwitterPage()
    {
        return $this->schoolTwitterPage;
    }

    public function setSchoolTwitterPage(?string $schoolTwitterPage)
    {
        $this->schoolTwitterPage = $schoolTwitterPage;

        return $this;
    }

    public function getAllowEventCreation(): ?bool
    {
        return $this->allowEventCreation;
    }

    public function setAllowEventCreation(?bool $allowEventCreation): self
    {
        $this->allowEventCreation = $allowEventCreation;

        return $this;
    }

    /**
     * @return Collection|ReportShare[]
     */
    public function getReportShares(): Collection
    {
        return $this->reportShares;
    }

    public function addReportShare(ReportShare $reportShare): self
    {
        if (!$this->reportShares->contains($reportShare)) {
            $this->reportShares[] = $reportShare;
            $reportShare->addSchool($this);
        }

        return $this;
    }

    public function removeReportShare(ReportShare $reportShare): self
    {
        if ($this->reportShares->removeElement($reportShare)) {
            $reportShare->removeSchool($this);
        }

        return $this;
    }
}
