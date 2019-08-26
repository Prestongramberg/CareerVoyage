<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\MessageReadStatus;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolAdministratorRequest;
use App\Entity\SingleChat;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Mailer\RequestsMailer;
use App\Model\Message;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\NewCompanyRequestRepository;
use App\Repository\RequestRepository;
use App\Repository\SingleChatRepository;
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
     * @Route("/chats/users/{id}", name="user_chats", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUserChats(Request $request, User $user) {

        $chats = $this->chatRepository->findByUser($user);

        $json = $this->serializer->serialize($chats, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
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
     * Creates a chat with a user if it doesn't exist or returns the existing chat if it does exist
     *
     * @Route("/chats/create/single", name="create_single_chat", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     */
    public function createSingleChat(Request $request) {

        $loggedInUser = $this->getUser();

        // the user id whom you want to message
        $userId = $request->request->get('userId');
        $user = $this->userRepository->find($userId);

        $possibleUsers[] =  $loggedInUser->getId();
        $possibleUsers[] =  $user->getId();

        $singleChat = $this->singleChatRepository->findByUsers($possibleUsers);

        // if a chat doesn't exist then let's create one!
        if(!$singleChat) {
            $singleChat = new SingleChat();
            $singleChat->addUser($user);
            $singleChat->addUser($loggedInUser);
            $singleChat->setInitializedBy($loggedInUser);

            $this->entityManager->persist($singleChat);
            $this->entityManager->flush();
        } else {
            // if a chat does exist, let's mark all the messages as read for the logged in user
            $messageStatuses = $this->messageReadStatusRepository->getUnreadyMessagesByChatAndUser($singleChat, $loggedInUser);
            /** @var MessageReadStatus $messageStatus */
            foreach($messageStatuses as $messageStatus) {
                $messageStatus->setIsRead(true);
            }
            $this->entityManager->flush();
        }

        $json = $this->serializer->serialize($singleChat, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
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
     * sends a single message to a chat
     *
     * @Route("/chats/{id}/message", name="message_chat", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat $chat
     * @return JsonResponse
     * @throws \Exception
     */
    public function message(Request $request, Chat $chat, $pusherAppId, $pusherAppKey, $pusherAppSecret) {

        $loggedInUser = $this->getUser();

        $body = $request->request->get('message');
        $message = new ChatMessage();
        $message->setBody($body);
        $message->setSentFrom($loggedInUser);
        $message->setSentAt(new \DateTime());
        $message->setChat($chat);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // setup the users to send the message to
        $possibleUsers = [];
        $possibleUsers = array_filter($chat->getUsers()->toArray(), function(User $user) use ($loggedInUser) {
            return $user->getId() !== $loggedInUser->getId();
        });

        foreach($possibleUsers as $possibleUser) {
            $messageReadStatus = new MessageReadStatus();
            $messageReadStatus->setChatMessage($message);
            $messageReadStatus->setUser($possibleUser);
            $this->entityManager->persist($messageReadStatus);
        }

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

        $json = $this->serializer->serialize($chat, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
        $payload = json_decode($json, true);

        $data = [];
        $data['chat'] = $payload;
        $pusher->trigger(sprintf('chat-%s', $chat->getUid()), 'send-message', $data);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * sends a single message to a chat
     *
     * @Route("/chats/{id}/messages/unread", name="unread_messages_chat", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @param Chat $chat
     * @return JsonResponse
     * @throws \Exception
     */
    public function unreadMessages(Request $request, Chat $chat) {

        $loggedInUser = $this->getUser();

        $body = $request->request->get('message');
        $message = new Message();
        $message->setBody($body);
        $message->setFrom($loggedInUser);
        $message->setSentAt(new \DateTime());

        $chat->addMessage($message);

        $this->entityManager->persist($chat);
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

        $json = $this->serializer->serialize($chat, 'json', ['groups' => ['CHAT', 'MESSAGE']]);
        $payload = json_decode($json, true);

        $data = [];
        $data['chat'] = $payload;
        $pusher->trigger(sprintf('chat-%s', $chat->getUid()), 'send-message', $data);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/chats/create/group", name="create_group_chat", methods={"POST"}, options = { "expose" = true })
     */
    public function createGroupChat() {

        return new JsonResponse(
            [
                'success' => false,
                'message' => 'logic for this route not built yet',

            ], Response::HTTP_OK
        );

    }
}