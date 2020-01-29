<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
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

            if( $chatUser->getId() === $this->getUser()->getId() ) {
                continue;
            }

            $chatUser = json_decode($this->serializer->serialize($chatUser, 'json', ['groups' => ['CHAT']]), true);
            $data['user'] = $chatUser;

            $unreadMessages = $this->chatMessageRepository->findBy([
                'sentTo' => $user,
                'hasBeenRead' => false,
                'chat' => $chat
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
     * @Route("/chats/search-users", name="search_chat_users", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function searchChatUsers(Request $request) {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $users = $this->getChattableUsers($loggedInUser, $request->query->get('search', ''));
        $payload = json_decode($this->serializer->serialize($users, 'json', ['groups' => ['ALL_USER_DATA']]), true);

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
     * @throws \Doctrine\DBAL\DBALException
     */
    public function createOrGetChat(Request $request) {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        // the user id whom you want to message
        $data = json_decode($request->getContent(), true);
        $userId = $data["userId"];
        $user = $this->userRepository->find($userId);

        $chattableUsers = $this->getChattableUsers($loggedInUser);

        $userWishingToChatWith = array_filter($chattableUsers, function($chattableUser) use($user) {
            return $user->getId() == $chattableUser['id'];
        });

        if(empty($userWishingToChatWith)) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'You do not have permission to talk with that user'
                ],
                Response::HTTP_OK
            );
        }

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

        // let's go ahead and mark any messages in the chat that have been sent to you as read
        foreach($chat->getMessages() as $message) {
            if($message->getSentTo()->getId() === $loggedInUser->getId()) {
                $message->setHasBeenRead(true);
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            }
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
     * @Route("/chats/{chatId}/read", name="chat_read_messages", methods={"POST"}, options = { "expose" = true })
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
     * @Route("/chats/{chatId}/unread", name="get_unread_chat_messages", methods={"POST"}, options = { "expose" = true })
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
     * @Route("/chats/{chatId}/message", name="message_chat", methods={"POST"}, options = { "expose" = true })
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

        $data = json_decode($request->getContent(), true);
        $body = $data["message"];
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

    /**
     * @param User $loggedInUser
     * @param string $search
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getChattableUsers(User $loggedInUser, $search = '') {

        $users = [];

        /**
         * Students can message
         * 1. Educators that are part of the same school
         * 2. School administrators that are part of the same school
         * 3. Students that are part of the same school
         * @var StudentUser $loggedInUser
         */
        if($loggedInUser->isStudent()) {
            $educatorUsers = $this->educatorUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            // for now we are disabling student to student communication
            //$studentUsers = $this->studentUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $professionalUsers = $this->professionalUserRepository->findByAllowedCommunication($search, $loggedInUser);
            $users = array_merge($professionalUsers, $educatorUsers, $schoolAdministrators);
        }

        /**
         * Educators can message
         * 1. Educators that are part of the same school
         * 2. School administrators that are part of the same school
         * 3. Students that are part of the same school
         * 4. All Professional Users
         * @var EducatorUser $loggedInUser
         */
        if($loggedInUser->isEducator()) {
            $educatorUsers = $this->educatorUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $studentUsers = $this->studentUserRepository->findBySearchTermAndSchool($search, $loggedInUser->getSchool());
            $professionalUsers = $this->professionalUserRepository->findBySearchTerm($search);
            $users = array_merge($educatorUsers, $schoolAdministrators, $studentUsers, $professionalUsers);
        }

        /**
         * Professionals can message
         * 1. All educators on the platform
         * 2. All school administrators
         * 4. All Professional Users
         * @var ProfessionalUser $loggedInUser
         */
        if($loggedInUser->isProfessional()) {
            $educatorUsers = $this->educatorUserRepository->findBySearchTerm($search);
            $schoolAdministrators = $this->schoolAdministratorRepository->findBySearchTerm($search);
            $professionalUsers = $this->professionalUserRepository->findBySearchTerm($search);
            $studentUsers = $this->studentUserRepository->findByAllowedCommunication($search, $loggedInUser);
            $users = array_merge($studentUsers, $educatorUsers, $schoolAdministrators, $professionalUsers);
        }

        return $users;
    }
}
