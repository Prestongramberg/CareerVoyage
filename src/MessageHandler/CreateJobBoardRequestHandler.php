<?php

namespace App\MessageHandler;

use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\Request;
use App\Entity\RequestAction;
use App\Entity\RequestPossibleApprovers;
use App\Entity\RolesWillingToFulfill;
use App\Message\CreateJobBoardRequest;
use App\Util\ServiceHelper;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @see     https://symfony.com/doc/4.2/messenger.html
 * Class NewEventNotificationHandler
 * @package App\MessageHandler
 */
class CreateJobBoardRequestHandler implements MessageHandlerInterface
{
    use ServiceHelper;

    public function __invoke(CreateJobBoardRequest $message)
    {

        $requestId = $message->getRequestId();
        $request   = $this->requestRepository->find($requestId);

        if (!$request) {
            return;
        }

        foreach ($this->generateProfessionalCollection() as $result) {

            /** @var ProfessionalUser $professional */
            $professional = $result[0] ?? null;

            if (!$professional) {
                continue;
            }

            $results = $this->requestRepository->search($request->getCreatedBy(), Request::REQUEST_TYPE_JOB_BOARD, $professional, null, $request->getId());

            if (!empty($results)) {

                /** @var Request $jobBoardRequest */
                $jobBoardRequest = $results[0];
                $messages        = $jobBoardRequest->getNotification()['messages'] ?? [];
                $createdOn       = $jobBoardRequest->getNotification()['body']['Created On'];

                $jobBoardRequest->setNotification([
                    'title' => "<strong>{$request->getCreatedBy()->getFullName()}</strong> posted a new job board request - \"{$request->getSummary()}\"",
                    'data' => [
                        'educator_id' => $request->getCreatedBy()->getId(),
                    ],
                    'user_photo' => $request->getCreatedBy()->getPhotoPath(),
                    'user_photos' => [],
                    'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
                    'messages' => $messages,
                    'body' => [
                        'Request Type' => [
                            'order' => 1,
                            'value' => "<a target='_blank' href='{$this->router->generate('view_request', ['id' => $jobBoardRequest->getId()])}'>Job Board Request</a>",
                        ],
                        'Initiated By' => [
                            'order' => 2,
                            'value' => "<a target='_blank' href='{$this->router->generate('profile_index', ['id' => $request->getCreatedBy()->getId()])}'>{$jobBoardRequest->getCreatedBy()->getFullName()}</a>",
                        ],
                        'Summary' => [
                            'order' => 3,
                            'value' => "<a target='_blank' href='{$this->router->generate('view_request', ['id' => $jobBoardRequest->getId()])}'>{$request->getSummary()}</a>",
                        ],
                        'Description' => [
                            'order' => 4,
                            'value' => $request->getDescription(),
                        ],
                        'Volunteers Needed' => [
                            'order' => 5,
                            'value' => implode(", ", array_map(function (RolesWillingToFulfill $rolesWillingToFulfill) {
                                return $rolesWillingToFulfill->getName();
                            }, $request->getVolunteerRoles()->toArray())),
                        ],
                        'Volunteer Career Sector(s)' => [
                            'order' => 6,
                            'value' => implode(", ", array_map(function (Industry $industry) {
                                return $industry->getName();
                            }, $request->getPrimaryIndustries()->toArray())),
                        ],
                        'Created On' => $createdOn,
                    ],
                ]);

                $jobBoardRequest->setStatusLabel($request->getStatusLabel());
                $jobBoardRequest->setStatus($request->getStatus());
                $jobBoardRequest->setDescription($request->getDescription());
                $jobBoardRequest->setPublished($request->getPublished());
                $jobBoardRequest->setSummary($request->getSummary());
                $jobBoardRequest->setOpportunityType($request->getOpportunityType());

                foreach($jobBoardRequest->getRequestPossibleApprovers() as $possibleApprover) {

                    if($possibleApprover->getPossibleApprover()->getId() === $jobBoardRequest->getCreatedBy()->getId()) {
                        continue;
                    }

                    $possibleApprover->setNotificationTitle("<strong>{$request->getCreatedBy()->getFullName()}</strong> posted a new job board request - \"{$request->getSummary()}\"");
                }

                $this->entityManager->flush();
                continue;
            }

            // todo we need to do an edit here on all the request objects already assigned to the professioals

            $jobBoardRequest = clone $request;

            $this->entityManager->persist($jobBoardRequest);
            $this->entityManager->flush();

            $createdOn = $jobBoardRequest->getNotification()['body']['Created On'];

            $jobBoardRequest->setNotification([
                'title' => "<strong>{$request->getCreatedBy()->getFullName()}</strong> posted a new job board request - \"{$jobBoardRequest->getSummary()}\"",
                'data' => [
                    'educator_id' => $jobBoardRequest->getCreatedBy()->getId(),
                ],
                'user_photo' => $jobBoardRequest->getCreatedBy()->getPhotoPath(),
                'user_photos' => [],
                'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
                'messages' => [],
                'body' => [
                    'Request Type' => [
                        'order' => 1,
                        'value' => "<a target='_blank' href='{$this->router->generate('view_request', ['id' => $jobBoardRequest->getId()])}'>Job Board Request</a>",
                    ],
                    'Initiated By' => [
                        'order' => 2,
                        'value' => "<a target='_blank' href='{$this->router->generate('profile_index', ['id' => $jobBoardRequest->getCreatedBy()->getId()])}'>{$jobBoardRequest->getCreatedBy()->getFullName()}</a>",
                    ],
                    'Summary' => [
                        'order' => 3,
                        'value' => "<a target='_blank' href='{$this->router->generate('view_request', ['id' => $jobBoardRequest->getId()])}'>{$jobBoardRequest->getSummary()}</a>",
                    ],
                    'Description' => [
                        'order' => 4,
                        'value' => $jobBoardRequest->getDescription(),
                    ],
                    'Volunteers Needed' => [
                        'order' => 5,
                        'value' => implode(", ", array_map(function (RolesWillingToFulfill $rolesWillingToFulfill) {
                            return $rolesWillingToFulfill->getName();
                        }, $jobBoardRequest->getVolunteerRoles()->toArray())),
                    ],
                    'Volunteer Career Sector(s)' => [
                        'order' => 6,
                        'value' => implode(", ", array_map(function (Industry $industry) {
                            return $industry->getName();
                        }, $jobBoardRequest->getPrimaryIndustries()->toArray())),
                    ],
                    'Created On' => $createdOn,
                ],
            ]);

            $requestActionUrl = $this->router->generate('request_action', [
                'request_id' => $jobBoardRequest->getId(),
            ]);

            $jobBoardRequest->setActionUrl($requestActionUrl);
            $jobBoardRequest->setParentRequest($request);

            $possibleApprover = new RequestPossibleApprovers();
            $possibleApprover->setPossibleApprover($professional);
            $possibleApprover->setRequest($jobBoardRequest);
            $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE]);
            $possibleApprover->setHasNotification(true);
            $possibleApprover->setNotificationTitle("<strong>{$request->getCreatedBy()->getFullName()}</strong> posted a new job board request - \"{$jobBoardRequest->getSummary()}\"");
            $this->entityManager->persist($possibleApprover);

            $this->entityManager->flush();
        }
    }

    /**
     * @return iterable
     */
    private function generateProfessionalCollection(): iterable
    {
        $queryBuilder = $this->professionalUserRepository->createQueryBuilder('p')->getQuery();

        /** @var ProfessionalUser $professionalUser */
        foreach ($queryBuilder->iterate() as $professionalUser) {

            yield $professionalUser;
        }
    }
}