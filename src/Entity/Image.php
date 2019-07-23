<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"image" = "Image", "companyPhoto" = "CompanyPhoto", "companyResource" = "CompanyResource", "experienceFile" = "ExperienceFile", "experienceWaver" = "ExperienceWaver"})
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $fileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $originalName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $mimeType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyResource", mappedBy="company", orphanRemoval=true)
     */
    private $companyResources;

    public function __construct()
    {
        $this->companyResources = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return mixed
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param mixed $originalName
     */
    public function setOriginalName($originalName): void
    {
        $this->originalName = $originalName;
    }

    /**
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param mixed $mimeType
     */
    public function setMimeType($mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return Collection|CompanyResource[]
     */
    public function getCompanyResources(): Collection
    {
        return $this->companyResources;
    }

    public function addCompanyResource(CompanyResource $companyResource): self
    {
        if (!$this->companyResources->contains($companyResource)) {
            $this->companyResources[] = $companyResource;
            $companyResource->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyResource(CompanyResource $companyResource): self
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
}
