<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyPhotoRepository")
 */
class CompanyPhoto extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyPhotos")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;


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
        return UploaderHelper::COMPANY_PHOTO.'/'.$this->getFileName();
    }
}
