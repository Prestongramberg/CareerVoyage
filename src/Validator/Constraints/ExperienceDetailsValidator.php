<?php

namespace App\Validator\Constraints;

use App\Entity\Experience;
use App\Entity\User;
use App\Service\Geocoder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use SKAgarwal\GoogleApi\PlacesApi;

/**
 * @package App\Validator\Constraints
 */
class ExperienceDetailsValidator extends ConstraintValidator
{
    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * @param Geocoder $geocoder
     */
    public function __construct(Geocoder $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    /**
     * @param mixed      $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {

        if (!$protocol instanceof Experience) {
            return;
        }

        if ($protocol->getUtcStartDateAndTime() >= $protocol->getUtcEndDateAndTime()) {
            $this->context->buildViolation("Please enter a start date and time that is less than your end date and time.")
                          ->atPath('startDate')
                          ->addViolation();

            $this->context->buildViolation("Please enter an end date and time that is greater than your start date and time.")
                          ->atPath('endDate')
                          ->addViolation();

            $this->context->buildViolation("")->atPath('startTime')->addViolation();

            $this->context->buildViolation("")->atPath('endTime')->addViolation();
        }

        if (!$protocol->getZipcode() || !$protocol->getState() || !$protocol->getStreet() || !$protocol->getCity()) {
            $this->context->buildViolation("Please enter a valid address for your experience.")
                          ->atPath('addressSearch')
                          ->addViolation();
        }

    }
}