<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolAdministratorRequest;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Mailer\RequestsMailer;
use App\Model\Message;
use App\Repository\ChatRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\NewCompanyRequestRepository;
use App\Repository\RequestRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Pusher\Pusher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class ChatController
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chats(Request $request) {

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
     * @param User $user
     * @return JsonResponse
     */
    public function getChatHistory(Request $request, User $user) {

        /** @var Chat[] $chats */
        $chats = $this->chatRepository->findByUser($user);

        $payload = [];
        foreach($chats as $chat) {
            $data = [];
            $chatUser = $chat->getUserOne()->getId() === $user->getId() ? $chat->getUserTwo() : $chat->getUserOne();
            $chatUser = json_decode($this->serializer->serialize($chatUser, 'json', ['groups' => ['CHAT']]), true);
            $data['user'] = $chatUser;

            $unreadMessages = $this->chatMessageRepository->findBy([
                'sentTo' => $user,
                'hasBeenRead' => false
            ]);

            $data['unread_messages'] = count($unreadMessages);
            $payload[] = $data;
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
     * Creates a chat with a user if it doesn't exist or returns the existing chat if it does exist
     *
     * @Route("/chats/create", name="create_or_get_chat", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     */
    public function createOrGetChat(Request $request) {

        $loggedInUser = $this->getUser();

        // the user id whom you want to message
        $userId = $request->request->get('userId');
        $user = $this->userRepository->find($userId);

        $chat = $this->chatRepository->findOneBy([
            'userOne' => $loggedInUser,
            'userTwo' => $user
        ]);

        if(!$chat) {
            $chat = $this->chatRepository->findOneBy([
                'userOne' => $user,
                'userTwo' => $loggedInUser
            ]);
        }

        // if a chat doesn't exist then let's create one!
        if(!$chat) {
            $chat = new Chat();
            $chat->setUserOne($user);
            $chat->setUserTwo($loggedInUser);
            $this->entityManager->persist($chat);
            $this->entityManager->flush();
        }

        $json = $this->serializer->serialize($chat, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
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
     * @Route("/chats/{id}/read", name="chat_read_messages", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat $chat
     * @return JsonResponse
     */
    public function markChatMessagesAsRead(Request $request, Chat $chat) {

        $loggedInUser = $this->getUser();

        foreach($chat->getMessages() as $message) {

            // only mark the messages as read that have been sent to you
            if($message->getSentTo()->getId() === $loggedInUser->getId()) {
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
     * @Route("/chats/{id}/unread", name="get_unread_chat_messages", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat $chat
     * @return JsonResponse
     * @throws \Exception
     */
    public function getUnreadMessagesforChat(Request $request, Chat $chat) {

        $loggedInUser = $this->getUser();

        $unreadMessageCount = 0;
        foreach($chat->getMessages() as $message) {
            // only mark the messages as read that have been sent to you
            if($message->getSentTo()->getId() === $loggedInUser->getId()) {
                $unreadMessageCount++;
            }
        }

        return new JsonResponse(
            [
                'success' => true,
                'unreadMessageCount' => $unreadMessageCount
            ],
            Response::HTTP_OK
        );

    }

    /**
     * sends a single message to a chat
     *
     * @Route("/chats/{id}/message", name="message_chat", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat $chat
     * @param $pusherAppId
     * @param $pusherAppKey
     * @param $pusherAppSecret
     * @return JsonResponse
     * @throws \Pusher\PusherException
     * @throws \Exception
     */
    public function message(Request $request, Chat $chat, $pusherAppId, $pusherAppKey, $pusherAppSecret) {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $body = $request->request->get('message');
        $message = new ChatMessage();
        $message->setBody($body);
        $message->setSentFrom($loggedInUser);
        $message->setSentAt(new \DateTime());
        $message->setChat($chat);

        // Figure out which user to message from the chat object
        $userToMessage = $chat->getUserOne()->getId() === $loggedInUser->getId() ? $chat->getUserTwo() : $chat->getUserOne();
        $message->setSentTo($userToMessage);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $options = array(
            'cluster' => 'us2',
            'useTLS' => true,
        );
        $pusher = new Pusher(
            $pusherAppKey,
            $pusherAppSecret,
            $pusherAppId,
            $options
        );

        // Let's go ahead and actually send the message to each possible user
        $json = $this->serializer->serialize($chat, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
        $payload = json_decode($json, true);
        $data = [];
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
}
