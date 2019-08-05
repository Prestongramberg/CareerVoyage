<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CompanyRequest
 * @package App\Model
 */
class CompanyInvite
{
    /**
     * @var string
     * @Assert\NotBlank(message="Don't forget an email for your request!")
     */
    protected $emailAddress;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     * @return CompanyInvite
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

}