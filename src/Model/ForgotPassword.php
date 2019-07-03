<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ForgotPassword
 * @package App\Model
 */
class ForgotPassword
{

    /**
     * @var string
     * @Assert\Email()
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
     * @return ForgotPassword
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

}