<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyResourceRepository")
 */
class CompanyResource extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyResources")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @Groups({"COMPANY_RESOURCE"})
     * @Assert\NotBlank(message="Don't forget a title for your resource!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @Groups({"COMPANY_RESOURCE"})
     * @Assert\NotBlank(message="Don't forget a description for your resource!", groups={"EDIT"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var File
     */
    private $file;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $linkToWebsite;


    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPath()
    {
        return UploaderHelper::COMPANY_RESOURCE.'/'.$this->getFileName();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

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

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getLinkToWebsite(): ?string
    {
        return $this->linkToWebsite;
    }

    public function setLinkToWebsite(?string $linkToWebsite): self
    {
        $this->linkToWebsite = $linkToWebsite;

        return $this;
    }
}
