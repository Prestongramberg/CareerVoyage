<?php

namespace App\MessageHandler;

use App\Entity\ProfessionalUser;
use App\Entity\Request;
use App\Entity\RequestAction;
use App\Entity\RequestPossibleApprovers;
use App\Message\CreateJobBoardRequest;
use App\Util\ServiceHelper;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class NewEventNotificationHandler
 * @package App\MessageHandler
 */
class CreateJobBoardRequestHandler implements MessageHandlerInterface
{
    use ServiceHelper;

    public function __invoke(CreateJobBoardRequest $message)
    {

        $requestId = $message->getRequestId();
        $request = $this->requestRepository->find($requestId);

        if(!$request) {
            return;
        }

        foreach ($this->generateProfessionalCollection() as $result) {

            /** @var ProfessionalUser $professional */
            $professional = $result[0] ?? null;

            if (!$professional) {
                continue;
            }

            // todo we need to do an edit here on all the request objects already assigned to the professioals

            $jobBoardRequest = clone $request;

            $this->entityManager->persist($jobBoardRequest);
            $this->entityManager->flush();

            $requestActionUrl = $this->router->generate('request_action', [
                'request_id' => $jobBoardRequest->getId(),
            ]);

            $jobBoardRequest->setActionUrl($requestActionUrl);
            $jobBoardRequest->setParentRequest($request);

            $requestAction = new RequestAction();
            $requestAction->setUser($jobBoardRequest->getCreatedBy());
            $requestAction->setRequest($jobBoardRequest);
            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_CREATE);
            $this->entityManager->persist($requestAction);

            $possibleApprover = new RequestPossibleApprovers();
            $possibleApprover->setPossibleApprover($professional);
            $possibleApprover->setRequest($jobBoardRequest);
            $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE]);
            $possibleApprover->setNotificationTitle("<strong>{$request->getCreatedBy()->getFullName()}</strong> posted a new job board request - \"{$jobBoardRequest->getSummary()}\"");
            $this->entityManager->persist($possibleApprover);

            $this->entityManager->flush();

            break;

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