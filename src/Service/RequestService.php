<?php

namespace App\Service;

use App\Entity\CompanyExperience;
use App\Entity\Experience;
use App\Entity\Registration;
use App\Entity\Request;
use App\Entity\RequestAction;
use App\Entity\RequestPossibleApprovers;
use App\Entity\SchoolExperience;
use App\Entity\User;
use App\Mailer\RequestsMailer;
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
     * @var RequestsMailer
     */
    private $requestsMailer;

    /**
     * RequestService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RouterInterface        $router
     * @param RequestsMailer         $requestsMailer
     */
    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router,
                                RequestsMailer $requestsMailer
    ) {
        $this->entityManager  = $entityManager;
        $this->router         = $router;
        $this->requestsMailer = $requestsMailer;
    }

    /**
     * @param User       $createdBy
     * @param User       $userToRegister
     * @param Experience $experience
     *
     * @return \App\Entity\Request
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createRegistrationRequest(User $createdBy, User $userToRegister, Experience $experience)
    {

        $skip = (
            !$experience instanceof SchoolExperience &&
            !$experience instanceof CompanyExperience
        );

        if ($skip) {
            return null;
        }

        $registration = new Registration();
        $registration->setUser($userToRegister);
        $registration->setExperience($experience);
        $registration->setApproved(true);
        $registration->setStatus(Request::REQUEST_STATUS_APPROVED);

        if($experience->getRequireApproval()) {
            $registration->setApproved(false);
            $registration->setStatus(Request::REQUEST_STATUS_PENDING);
        }

        $this->entityManager->persist($registration);
        $this->entityManager->flush();

        $registrationRequest = new \App\Entity\Request();
        $registrationRequest->setRequestType(\App\Entity\Request::REQUEST_TYPE_NEW_REGISTRATION);
        $registrationRequest->setCreatedBy($createdBy);
        $registrationRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING);
        $registrationRequest->setStatusLabel('Registration Pending Approval');

        $notification = [
            'title' => "<strong>{$createdBy->getFullName()}</strong> has registered for experience: \"{$experience->getTitle()}\"",
            'user_photo' => $createdBy->getPhotoPath(),
            'user_photos' => [],
            'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
            'messages' => [],
            'registration_id' => $registration->getId(),
            'data' => [
                'user_to_register' => $userToRegister->getId(),
            ],
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
        ];

        if($createdBy->getId() !== $userToRegister->getId()) {
            $notification['title'] = "<strong>{$createdBy->getFullName()}</strong> has registered \"{$userToRegister->getFullName()}\" for experience: \"{$experience->getTitle()}\"";
            $notification['body']['User To Register'] = [
                'order' => 7,
                'value' => $userToRegister->getFullName()
            ];
        }

        $registrationRequest->setNotification($notification);

        $this->entityManager->persist($registrationRequest);
        $this->entityManager->flush();

        $requestActionUrl = $this->router->generate('request_action', [
            'experience_id' => $experience->getId(),
            'user_id' => $userToRegister->getId(),
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

        if($createdBy->getId() !== $userToRegister->getId()) {
            $createdByApprover->setNotificationTitle("<strong>You</strong> have registered \"{$userToRegister->getFullName()}\" for experience: \"{$experience->getTitle()}\"");

            $registeredUserApprover = new RequestPossibleApprovers();
            $registeredUserApprover->setPossibleApprover($userToRegister);
            $registeredUserApprover->setRequest($registrationRequest);
            $registeredUserApprover->setNotificationDate(new \DateTime());
            $registeredUserApprover->setPossibleActions([
                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
            ]);
            $registeredUserApprover->setNotificationTitle("<strong>{$createdBy->getFullName()}</strong> has registered you for experience: \"{$experience->getTitle()}\"");
            $this->entityManager->persist($registeredUserApprover);
        }

        if (!$experience->getRequireApproval()) {
            $registrationRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                ->setStatusLabel('Registration Has Been Approved');

            $createdByApprover->setPossibleActions([
                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                RequestAction::REQUEST_ACTION_NAME_UNREGISTER,
            ]);
        }

        $approver = null;
        if ($experience instanceof SchoolExperience && $schoolContact = $experience->getSchoolContact()) {
            $approver = $experience->getSchoolContact();
        }

        if ($experience instanceof CompanyExperience && $employeeContact = $experience->getEmployeeContact()) {
            $approver = $experience->getEmployeeContact();
        }

        if ($approver) {
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
                    RequestAction::REQUEST_ACTION_NAME_VIEW_REGISTRATION_LIST,
                ]
            );

            $creatorPrefix = "<strong>{$createdBy->getFullName()}</strong> has";

            if($approver->getId() === $createdBy->getId()) {
                $creatorPrefix = "<strong>You</strong> have";
            }

            $possibleApprover->setNotificationTitle("{$creatorPrefix} registered for experience: \"{$experience->getTitle()}\"");
            if($createdBy->getId() !== $userToRegister->getId()) {
                $possibleApprover->setNotificationTitle("{$creatorPrefix} registered \"{$userToRegister->getFullName()}\" for experience: \"{$experience->getTitle()}\"");
            }

            $this->entityManager->persist($possibleApprover);

            $this->requestsMailer->userRegistrationApproval($approver, $createdBy, $experience);
        }

        $this->entityManager->persist($registrationRequest);
        $this->entityManager->persist($createdByApprover);

        $this->entityManager->flush();

        return $registrationRequest;
    }
}