<?php

namespace App\MessageHandler;

use App\Message\RecapMessage;
use App\Message\UnseenMessagesMessage;
use App\Repository\UserRepository;
use App\Util\ServiceHelper;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * This class is responsible for putting together the recap.
 *
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class UnseenMessagesHandler
 * @package App\MessageHandler
 */
class UnseenMessagesHandler implements MessageHandlerInterface
{
    use ServiceHelper;

    public function __invoke(UnseenMessagesMessage $message)
    {
        $userId = $message->getUserId();
        $user = $this->userRepository->find($userId);

        $chatMessages = $this->chatMessageRepository->findBy([
            'sentTo' => $user,
            'hasBeenRead' => false
        ]);

        $this->recapMailer->send($user, $chatMessages);

        echo 'completed...';
    }
}