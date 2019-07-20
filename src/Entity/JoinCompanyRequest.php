<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JoinCompanyRequestRepository")
 */
class JoinCompanyRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="joinCompanyRequests")
     * @ORM\JoinColumn(nullable=false)
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
