<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\User;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Pusher\Pusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ChatController
 *
 * @package App\Controller
 * @Route("/dashboard")
 */
class ChatController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * 1. Get all the possible users that the logged in user is able to message
     * 2.
     *
     *
     * @Route("/chats", name="chats", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chats(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        //todo I'm sure you wont' be able to message every user in the system
        //this will need to be refactored to fix that
        $chattableUsers = $this->userRepository->findAll();

        return $this->render('chat/index.html.twig', [
            'user' => $user,
            'chattableUsers' => $chattableUsers,
        ]);
    }

    /**
     * @Route("/chats/users/{id}/history", name="get_chat_history", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getChatHistory(Request $request, User $user)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var Chat[] $chats */
        $chats = $this->chatRepository->findByUser($user);

        $payload = [];
        foreach ($chats as $chat) {
            $data     = [];
            $chatUser = $chat->getUserOne()->getId() === $user->getId() ? $chat->getUserTwo() : $chat->getUserOne();

            if(!$chatUser) {
                continue;
            }

            if ($chatUser->getId() === $this->getUser()->getId()) {
                continue;
            }

            $chattableUsers   = $this->chatHelper->getChattableUsers($loggedInUser);
            $chattableUserIds = [];
            foreach ($chattableUsers as $chattableUser) {
                if (!empty($chattableUser['id'])) {
                    $chattableUserIds[] = $chattableUser['id'];
                }
            }

            if (!in_array($chatUser->getId(), $chattableUserIds)) {
                continue;
            }


            $chatUser     = json_decode($this->serializer->serialize($chatUser, 'json', ['groups' => ['CHAT']]), true);
            $data['user'] = $chatUser;

            $unreadMessages = $this->chatMessageRepository->findBy([
                'sentTo' => $user,
                'hasBeenRead' => false,
                'chat' => $chat,
            ]);

            $data['unread_messages'] = count($unreadMessages);
            $payload[]               = $data;
        }

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/chats/search-users", name="search_chat_users", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function searchChatUsers(Request $request)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $users        = $this->chatHelper->getChattableUsers($loggedInUser, $request->query->get('search', ''));
        $payload      = json_decode($this->serializer->serialize($users, 'json', ['groups' => ['ALL_USER_DATA']]), true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Creates a chat with a user if it doesn't exist or returns the existing chat if it does exist
     *
     * @Route("/chats/create", name="create_or_get_chat", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function createOrGetChat(Request $request)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        // the user id whom you want to message
        $data   = json_decode($request->getContent(), true);
        $userId = $data["userId"];
        $user   = $this->userRepository->find($userId);

        $chattableUsers = $this->chatHelper->getChattableUsers($loggedInUser);

        $userWishingToChatWith = array_filter($chattableUsers, function ($chattableUser) use ($user) {
            return $user->getId() == $chattableUser['id'];
        });

        if (empty($userWishingToChatWith)) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'You do not have permission to talk with that user',
                ],
                Response::HTTP_OK
            );
        }

        $chat = $this->chatRepository->findOneBy([
            'userOne' => $loggedInUser,
            'userTwo' => $user,
        ]);

        if (!$chat) {
            $chat = $this->chatRepository->findOneBy([
                'userOne' => $user,
                'userTwo' => $loggedInUser,
            ]);
        }

        // if a chat doesn't exist then let's create one!
        if (!$chat) {
            $chat = new Chat();
            $chat->setUserOne($user);
            $chat->setUserTwo($loggedInUser);
            $chat->setUpdatedAt(new \DateTime('now'));
            $this->entityManager->persist($chat);
            $this->entityManager->flush();
        }

        // let's go ahead and mark any messages in the chat that have been sent to you as read
        foreach ($chat->getMessages() as $message) {
            if ($message->getSentTo()->getId() === $loggedInUser->getId()) {
                $message->setHasBeenRead(true);
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            }
        }

        $json    = $this->serializer->serialize($chat, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * 1. Mark the messages as read for the logged in user for that given chat
     *
     * @Route("/chats/{chatId}/read", name="chat_read_messages", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat    $chat
     *
     * @return JsonResponse
     */
    public function markChatMessagesAsRead(Request $request, Chat $chat)
    {

        $loggedInUser = $this->getUser();

        foreach ($chat->getMessages() as $message) {

            // only mark the messages as read that have been sent to you
            if ($message->getSentTo()->getId() === $loggedInUser->getId()) {
                $message->setHasBeenRead(true);
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            }
        }

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * sends a single message to a chat
     *
     * @Route("/chats/{chatId}/unread", name="get_unread_chat_messages", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat    $chat
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function getUnreadMessagesforChat(Request $request, Chat $chat)
    {

        $loggedInUser = $this->getUser();

        $unreadMessageCount = 0;
        foreach ($chat->getMessages() as $message) {
            // only mark the messages as read that have been sent to you
            if ($message->getSentTo()->getId() === $loggedInUser->getId()) {
                $unreadMessageCount++;
            }
        }

        return new JsonResponse(
            [
                'success' => true,
                'unreadMessageCount' => $unreadMessageCount,
            ],
            Response::HTTP_OK
        );

    }

    /**
     * sends a single message to a chat
     *
     * @Route("/chats/{chatId}/message", name="message_chat", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat    $chat
     * @param         $pusherAppId
     * @param         $pusherAppKey
     * @param         $pusherAppSecret
     *
     * @return JsonResponse
     * @throws \Pusher\PusherException
     * @throws \Exception
     */
    public function message(Request $request, Chat $chat, $pusherAppId, $pusherAppKey, $pusherAppSecret)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $body = $data["message"];
        $body = $this->generateUrlForChat($body);

        $message = new ChatMessage();
        $message->setBody($body);
        $message->setSentFrom($loggedInUser);
        $message->setSentAt(new \DateTime());
        $message->setChat($chat);

        // Figure out which user to message from the chat object
        $userToMessage = $chat->getUserOne()->getId() === $loggedInUser->getId() ? $chat->getUserTwo() : $chat->getUserOne();
        $message->setSentTo($userToMessage);

        $this->entityManager->persist($message);

        $chat->setUpdatedAt(new \DateTime('now'));
        $this->entityManager->persist($chat);

        $this->entityManager->flush();

        $options = array (
            'cluster' => 'us2',
            'useTLS' => true,
        );
        $pusher  = new Pusher(
            $pusherAppKey,
            $pusherAppSecret,
            $pusherAppId,
            $options
        );

        // Let's go ahead and actually send the message to each possible user
        $json         = $this->serializer->serialize($chat, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
        $payload      = json_decode($json, true);
        $data         = [];
        $data['chat'] = $payload;
        $pusher->trigger(sprintf('chat-%s', $userToMessage->getId()), 'send-message', $data);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    private function generateUrlForChat($text)
    {
        // https://stackoverflow.com/questions/1925455/how-to-mimic-stack-overflow-auto-link-behavior 
        // a more readably-formatted version of the pattern is on http://daringfireball.net/2010/07/improved_regex_for_matching_urls
        $pattern = '~(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))~';

        return preg_replace_callback($pattern, function ($matches) {

            $url       = array_shift($matches);
            $url_parts = parse_url($url);

            $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
            $text = preg_replace("/^www./", "", $text);

            // If we want to show everything but the last /folder change 30 to $last and uncomment
            // $last = -(strlen(strrchr($text, "/"))) + 1;
            // if ($last < 0) {
            $text = substr($text, 0, 30) . "&hellip;";

            // }

            return sprintf('<a href="%s" target="_blank">%s</a>', $url, $text);

        }, $text);
    }


    private function generateUrlForChat_OLD($string)
    {
        $link = "";

        if (preg_match("@^http|https://@i", $string)) {
            $link = '<a href="' . $string . '" target="_blank">' . $string . '</a>';
        } else {
            $link = '<a href="http://' . $string . '" target="_blank">' . $string . '</a>';
        }

        return $link;
    }
}
