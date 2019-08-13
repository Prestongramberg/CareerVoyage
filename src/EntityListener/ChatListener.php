<?php

namespace App\EntityListener;

use App\Entity\Chat;
use App\Entity\Image;
use App\Model\Message;
use App\Service\FileUploader;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ChatListener
{

    use ServiceHelper;

    /**
     * Make sure the file has a name before persisting
     *
     * @param Chat $chat
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Chat $chat, LifecycleEventArgs $args)
    {
        $this->serializeField($chat);
    }

    /**
     * Make sure the file has a name before updating
     *
     * @param Chat $chat
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Chat $chat, PreUpdateEventArgs $args)
    {
        $this->serializeField($chat);
    }

    /**
     * Deserialize the content property after loading
     *
     * @param Chat $chat
     * @param LifecycleEventArgs $args
     */
    public function postLoad(Chat $chat, LifecycleEventArgs $args)
    {
        $this->deserializeField($chat);
    }

    /**
     * Deserialize the field property for the Property entity
     *
     * @param Chat $chat
     */
    private function deserializeField(Chat $chat)
    {
        $messages = [];
        foreach($chat->getMessages() as $key => $message) {
            $messages[$key] = $this->serializer->deserialize(json_encode($message, true), Message::class, 'json');
        }

        $chat->setMessages($messages);
    }


    /**
     * @param Chat $chat
     */
    private function serializeField(Chat $chat) {
        $messages = [];
        foreach($chat->getMessages() as $key => $message) {
            $data = $this->serializer->serialize($message, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
            $messages[$key] = (json_decode($data, true));
        }
        $chat->setMessages($messages);
    }
}