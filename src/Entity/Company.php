<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 */
class Company
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget an address!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
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
     * @Assert\NotBlank(message="Don't forget a brief company description!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $briefCompanyDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyLinkedinPage;

    /**
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

    public function __construct()
    {
        $this->professionalUsers = new ArrayCollection();
        $this->companyImages = new ArrayCollection();
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

    public function getBriefCompanyDescription(): ?string
    {
        return $this->briefCompanyDescription;
    }

    public function setBriefCompanyDescription(?string $briefCompanyDescription): self
    {
        $this->briefCompanyDescription = $briefCompanyDescription;

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
}
