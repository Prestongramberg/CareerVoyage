<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyVideoRepository")
 */
class CompanyVideo extends Video
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyVideos")
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
}
