<?php

namespace App\Service;

use App\Entity\CompanyExperience;
use App\Entity\Experience;
use App\Entity\Registration;
use App\Entity\RequestAction;
use App\Entity\RequestPossibleApprovers;
use App\Entity\SchoolExperience;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class RequestService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * RequestService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RouterInterface        $router
     */
    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->router        = $router;
    }

    public function createRegistrationRequest(User $createdBy, Experience $experience)
    {

        $skip = (
            !$experience instanceof SchoolExperience &&
            !$experience instanceof CompanyExperience
        );

        if ($skip) {
            return null;
        }

        $registrationRequest = new \App\Entity\Request();
        $registrationRequest->setRequestType(\App\Entity\Request::REQUEST_TYPE_NEW_REGISTRATION);
        $registrationRequest->setCreatedBy($createdBy);
        $registrationRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING);
        $registrationRequest->setStatusLabel('Registration Pending Approval');

        $registrationRequest->setNotification([
            'title' => "<strong>{$createdBy->getFullName()}</strong> has registered for experience: \"{$experience->getTitle()}\"",
            'user_photo' => $createdBy->getPhotoPath(),
            'user_photos' => [],
            'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
            'messages' => [],
            'body' => [
                'Request Type' => [
                    'order' => 1,
                    'value' => 'Registration',
                ],
                'Initiated By' => [
                    'order' => 2,
                    'value' => "<a target='_blank' href='{$this->router->generate('profile_index', ['id' => $createdBy->getId()])}'>{$createdBy->getFullName()}</a>",
                ],
                'Experience' => [
                    'order' => 3,
                    'value' => "<a target='_blank' href='{$this->router->generate('experience_view', ['id' => $experience->getId()])}'>{$experience->getTitle()}</a>",
                ],
                'Created On' => [
                    'order' => 6,
                    'value' => (new \DateTime())->format("m/d/Y h:i A"),
                ],
            ],
        ]);

        $this->entityManager->persist($registrationRequest);
        $this->entityManager->flush();

        $requestActionUrl = $this->router->generate('request_action', [
            'experience_id' => $experience->getId(),
            'request_id' => $registrationRequest->getId(),
        ]);

        $registrationRequest->setActionUrl($requestActionUrl);

        $createdByApprover = new RequestPossibleApprovers();
        $createdByApprover->setPossibleApprover($createdBy);
        $createdByApprover->setRequest($registrationRequest);
        $createdByApprover->setNotificationDate(new \DateTime());
        $createdByApprover->setPossibleActions([
            RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
        ]);
        $createdByApprover->setNotificationTitle("<strong>You</strong> have registered for experience: \"{$experience->getTitle()}\"");

        if (!$experience->getRequireApproval()) {
            $registrationRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                ->setStatusLabel('Registration Has Been Approved');

            $createdByApprover->setPossibleActions([
                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                RequestAction::REQUEST_ACTION_NAME_UNREGISTER,
            ]);

            $registration = new Registration();
            $registration->setUser($registrationRequest->getCreatedBy());
            $registration->setExperience($experience);
            $this->entityManager->persist($registration);
        }

        $approver = null;
        if ($experience instanceof SchoolExperience && $schoolContact = $experience->getSchoolContact()) {
            $approver = $experience->getSchoolContact();
        }

        if ($experience instanceof CompanyExperience && $employeeContact = $experience->getEmployeeContact()) {
            $approver = $experience->getEmployeeContact();
        }

        if($approver) {
            $possibleApprover = new RequestPossibleApprovers();
            $possibleApprover->setPossibleApprover($approver);
            $possibleApprover->setRequest($registrationRequest);
            $possibleApprover->setHasNotification(true);

            $possibleApprover->setPossibleActions(
                [
                    RequestAction::REQUEST_ACTION_NAME_APPROVE,
                    RequestAction::REQUEST_ACTION_NAME_DENY,
                    RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                    RequestAction::REQUEST_ACTION_NAME_VIEW_REGISTRATION_LIST
                ]
            );

            $possibleApprover->setNotificationTitle("<strong>{$createdBy->getFullName()}</strong> has registered for experience: \"{$experience->getTitle()}\"");

            $this->entityManager->persist($possibleApprover);
        }

        $this->entityManager->persist($registrationRequest);
        $this->entityManager->persist($createdByApprover);

        $this->entityManager->flush();

        return $registrationRequest;
    }
}