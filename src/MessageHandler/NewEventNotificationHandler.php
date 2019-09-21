<?php

namespace App\MessageHandler;

use App\Message\NewEventNotificationMessage;
use App\Message\RecapMessage;
use App\Repository\UserRepository;
use App\Util\ServiceHelper;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class NewEventNotificationHandler
 * @package App\MessageHandler
 */
class NewEventNotificationHandler implements MessageHandlerInterface
{
    use ServiceHelper;

    public function __invoke(NewEventNotificationMessage $message)
    {
        $eventId = $message->getEventId();
        $event = $this->experienceRepository->find($eventId);
        $eventSecondaryIndustries = $event->getSecondaryIndustries();
        $educators = $this->educatorUserRepository->findBySecondaryIndustries($eventSecondaryIndustries);

        foreach($educators as $educator) {
            $educator = $this->educatorUserRepository->find($educator['id']);

            // the educator might not have setup their email yet and just has a username
            if(!$educator->getEmail()) {
                continue;
            }
            $this->newLessonMailer->send($educator, $lesson);
        }

        echo 'completed...';
    }
}