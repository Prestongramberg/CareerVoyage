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
class CompanyResource extends Resource
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyResources")
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
        return UploaderHelper::COMPANY_RESOURCE.'/'.$this->getFileName();
    }

}
