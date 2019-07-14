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
     * @ORM\OneToMany(targetEntity="CompanyVideo", mappedBy="company", orphanRemoval=true)
     */
    private $companyVideos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyDocument", mappedBy="company", orphanRemoval=true)
     */
    private $companyDocuments;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="boolean")
     */
    private $approved = false;

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

    public function __construct()
    {
        $this->professionalUsers = new ArrayCollection();
        $this->companyPhotos = new ArrayCollection();
        $this->companyVideos = new ArrayCollection();
        $this->companyDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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

    public function getCompanyLinkedinPage(): ?string
    {
        return $this->companyLinkedinPage;
    }

    public function setCompanyLinkedinPage(?string $companyLinkedinPage): self
    {
        $this->companyLinkedinPage = $companyLinkedinPage;

        return $this;
    }

    public function getPrimaryContact(): ?string
    {
        return $this->primaryContact;
    }

    public function setPrimaryContact(?string $primaryContact): self
    {
        $this->primaryContact = $primaryContact;

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
            $professionalUser->setCompany($this);
        }

        return $this;
    }

    public function removeProfessionalUser(ProfessionalUser $professionalUser): self
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
    public function getCompanyPhotos(): Collection
    {
        return $this->companyPhotos;
    }

    public function addCompanyPhoto(CompanyPhoto $companyPhoto): self
    {
        if (!$this->companyPhotos->contains($companyPhoto)) {
            $this->companyPhotos[] = $companyPhoto;
            $companyPhoto->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyPhoto(CompanyPhoto $companyPhoto): self
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
     * @return Collection|CompanyDocument[]
     */
    public function getCompanyDocuments(): Collection
    {
        return $this->companyDocuments;
    }

    public function addCompanyDocument(CompanyDocument $companyDocument): self
    {
        if (!$this->companyDocuments->contains($companyDocument)) {
            $this->companyDocuments[] = $companyDocument;
            $companyDocument->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyDocument(CompanyDocument $companyDocument): self
    {
        if ($this->companyDocuments->contains($companyDocument)) {
            $this->companyDocuments->removeElement($companyDocument);
            // set the owning side to null (unless already changed)
            if ($companyDocument->getCompany() === $this) {
                $companyDocument->setCompany(null);
            }
        }

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getPrimaryIndustry(): ?Industry
    {
        return $this->primaryIndustry;
    }

    public function setPrimaryIndustry(?Industry $primaryIndustry): self
    {
        $this->primaryIndustry = $primaryIndustry;

        return $this;
    }

    public function getThumbnailImage(): ?Image
    {
        return $this->thumbnailImage;
    }

    public function setThumbnailImage(?Image $thumbnailImage): self
    {
        $this->thumbnailImage = $thumbnailImage;

        return $this;
    }

    public function getFeaturedImage(): ?Image
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?Image $featuredImage): self
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }
}
