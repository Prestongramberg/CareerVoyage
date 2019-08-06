<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JoinCompanyRequestRepository")
 */
class JoinCompanyRequest extends Request
{
    const TYPE_COMPANY_TO_USER = 'TYPE_COMPANY_TO_USER';
    const TYPE_USER_TO_COMPANY = 'TYPE_USER_TO_COMPANY';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="joinCompanyRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isFromCompany = false;

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getIsFromCompany()
    {
        return $this->isFromCompany;
    }

    public function setIsFromCompany($isFromCompany)
    {
        $this->isFromCompany = $isFromCompany;

        return $this;
    }
}
