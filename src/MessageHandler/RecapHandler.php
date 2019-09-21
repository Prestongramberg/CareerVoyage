<?php

namespace App\MessageHandler;

use App\Message\RecapMessage;
use App\Repository\UserRepository;
use App\Util\ServiceHelper;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * This class is responsible for putting together the recap.
 *
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class NotificationHandler
 * @package App\MessageHandler
 */
class RecapHandler implements MessageHandlerInterface
{
    use ServiceHelper;

    public function __invoke(RecapMessage $message)
    {
        $userId = $message->getUserId();
        $user = $this->userRepository->find($userId);
        $userSecondaryIndustries = $user->getSecondaryIndustries();

        // Get relevant lessons for the user's secondary industry preferences
        $lessons = $this->lessonRepository->findBySecondaryIndustries($userSecondaryIndustries);

        // Get relevant events for the user's secondary industry preferences
        $experiences = [];
        if($user->isEducator() || $user->isStudent()) {
            $experiences = $this->companyExperienceRepository->findBySecondaryIndustries($userSecondaryIndustries);
        } elseif ($user->isProfessional()) {
            $experiences = $this->schoolExperienceRepository->findBySecondaryIndustries($userSecondaryIndustries);
        }

        $this->recapMailer->send($user, $lessons, $experiences);

        echo 'completed...';
    }
}