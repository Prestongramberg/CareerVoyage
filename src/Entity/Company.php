<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 */
class Company
{
    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget an address!")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a phone number!", groups={"CREATE", "EDIT"})
     * @Assert\Regex(
     *     pattern="/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/",
     *     match=true,
     *     message="The phone number needs to be in this format: xxx-xxx-xxx",
     *     groups={"CREATE", "EDIT"}
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyLinkedinPage;

    /**
     * @Groups({"RESULTS_PAGE"})
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
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a name!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a short description!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shortDescription;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a long description!", groups={"EDIT"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a website!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $website;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget an email address!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $emailAddress;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @Assert\NotBlank(message="Don't forget a primary industry!", groups={"CREATE", "EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Industry", inversedBy="companies")
     */
    private $primaryIndustry;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist", "remove"})
     */
    private $thumbnailImage;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist", "remove"})
     */
    private $featuredImage;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Video", mappedBy="company", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $videos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyResource", mappedBy="company", orphanRemoval=true, cascade={"persist"})
     */
    private $companyResources;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JoinCompanyRequest", mappedBy="company", orphanRemoval=true)
     */
    private $joinCompanyRequests;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\OneToOne(targetEntity="App\Entity\NewCompanyRequest", mappedBy="company", cascade={"persist", "remove"})
     */
    private $newCompanyRequest;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="ownedCompany", cascade={"persist", "remove"})
     */
    private $owner;

    public function __construct()
    {
        $this->professionalUsers = new ArrayCollection();
        $this->companyPhotos = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->companyResources = new ArrayCollection();
        $this->joinCompanyRequests = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(?string $address)
    {
        $this->address = $address;

        return $this;
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

    public function getCompanyLinkedinPage()
    {
        return $this->companyLinkedinPage;
    }

    public function setCompanyLinkedinPage(?string $companyLinkedinPage)
    {
        $this->companyLinkedinPage = $companyLinkedinPage;

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

    public function getFeaturedImagePath()
    {
        return UploaderHelper::FEATURE_IMAGE.'/'.$this->getFeaturedImage()->getFileName();
    }

    public function getThumbnailImagePath()
    {
        return UploaderHelper::THUMBNAIL_IMAGE.'/'.$this->getThumbnailImage()->getFileName();
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

    public function setEmailAddress(string $emailAddress)
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
     * @return Collection|Video[]
     */
    public function getVideos()
    {
        return $this->videos;
    }

    public function addVideo(Video $video)
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setCompany($this);
        }

        return $this;
    }

    public function removeVideo(Video $video)
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getCompany() === $this) {
                $video->setCompany(null);
            }
        }

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
     * @return Collection|JoinCompanyRequest[]
     */
    public function getJoinCompanyRequests()
    {
        return $this->joinCompanyRequests;
    }

    public function addJoinCompanyRequest(JoinCompanyRequest $joinCompanyRequest)
    {
        if (!$this->joinCompanyRequests->contains($joinCompanyRequest)) {
            $this->joinCompanyRequests[] = $joinCompanyRequest;
            $joinCompanyRequest->setCompany($this);
        }

        return $this;
    }

    public function removeJoinCompanyRequest(JoinCompanyRequest $joinCompanyRequest)
    {
        if ($this->joinCompanyRequests->contains($joinCompanyRequest)) {
            $this->joinCompanyRequests->removeElement($joinCompanyRequest);
            // set the owning side to null (unless already changed)
            if ($joinCompanyRequest->getCompany() === $this) {
                $joinCompanyRequest->setCompany(null);
            }
        }

        return $this;
    }

    public function getNewCompanyRequest()
    {
        return $this->newCompanyRequest;
    }

    public function setNewCompanyRequest(NewCompanyRequest $newCompanyRequest)
    {
        $this->newCompanyRequest = $newCompanyRequest;

        // set the owning side of the relation if necessary
        if ($this !== $newCompanyRequest->getCompany()) {
            $newCompanyRequest->setCompany($this);
        }

        return $this;
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
}
