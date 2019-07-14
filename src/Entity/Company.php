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
     * @Assert\NotBlank(message="Don't forget an address!", groups={"CREATE", "EDIT"})
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
     * @Assert\NotBlank(message="Don't forget a primary contact!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $primaryContact;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProfessionalUser", mappedBy="company")
     */
    private $professionalUsers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $heroImage;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyImage", mappedBy="company", orphanRemoval=true)
     */
    private $companyImages;

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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255)
     */
    private $shortDescription;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255)
     */
    private $website;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Column(type="string", length=255)
     */
    private $emailAddress;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Industry", inversedBy="companies")
     */
    private $primaryIndustry;

    public function __construct()
    {
        $this->professionalUsers = new ArrayCollection();
        $this->companyImages = new ArrayCollection();
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogoPath()
    {
        return UploaderHelper::COMPANY_LOGO.'/'.$this->getLogo();
    }

    public function getHeroImagePath()
    {
        return UploaderHelper::HERO_IMAGE.'/'.$this->getHeroImage();
    }

    public function getHeroImage(): ?string
    {
        return $this->heroImage;
    }

    public function setHeroImage(?string $heroImage): self
    {
        $this->heroImage = $heroImage;

        return $this;
    }

    /**
     * @return Collection|CompanyImage[]
     */
    public function getCompanyImages(): Collection
    {
        return $this->companyImages;
    }

    public function addCompanyImage(CompanyImage $companyImage): self
    {
        if (!$this->companyImages->contains($companyImage)) {
            $this->companyImages[] = $companyImage;
            $companyImage->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyImage(CompanyImage $companyImage): self
    {
        if ($this->companyImages->contains($companyImage)) {
            $this->companyImages->removeElement($companyImage);
            // set the owning side to null (unless already changed)
            if ($companyImage->getCompany() === $this) {
                $companyImage->setCompany(null);
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

    public function addCompanyVideoUrl(CompanyVideo $companyVideo): self
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
    
}
