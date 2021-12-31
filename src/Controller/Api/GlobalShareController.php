<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonFavorite;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\Share;
use App\Entity\StudentUser;
use App\Entity\SystemUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Form\ProfessionalEditProfileFormType;
use App\Model\GlobalShareFilters;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyRepository;
use App\Repository\ExperienceRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\SchoolExperienceRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class GlobalShareController
 *
 * @package App\Controller
 * @Route("/api/global-share")
 */
class GlobalShareController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/data", name="global_share_data", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDataAction(Request $request)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $filters = new GlobalShareFilters($request);

        $payload = $this->globalShare->getData($loggedInUser, $filters);

        // We need to get all the data without filters applied as well to show all the filter options on the front end.
        $payloadForFilters = $this->globalShare->getData($loggedInUser);

        $payload['filters'] = $payloadForFilters['all'];

        return new JsonResponse([
                'success' => true,
                'data'    => $payload,
            ], Response::HTTP_OK);
    }

    /**
     * @Route("/notify", name="api_global_share_notify", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function experienceNotifyUsersAction(Request $request)
    {
        $experienceId  = $request->request->get('experienceId', null);
        $requestId     = $request->request->get('requestId', null);
        $message       = $request->request->get('message', '');
        $userId        = $request->request->get('userId');
        $experience    = null;
        $requestEntity = null;

        if (empty(trim($message))) {
            return new JsonResponse([
                    'success' => false,
                    'message' => 'Please enter a message you would like to send.',
                ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        if ($experienceId) {
            $experience = $this->experienceRepository->find($experienceId);
        }

        if ($requestId) {
            $requestEntity = $this->requestRepository->find($requestId);
        }

        if (!$experience && !$requestEntity) {
            return new JsonResponse([
                    'success' => false,
                ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->userRepository->find($userId);

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
            $this->entityManager->persist($chat);
            $this->entityManager->flush();
        }

        $chatMessage = new ChatMessage();
        $chatMessage->setBody($message);
        $chatMessage->setSentFrom($loggedInUser);
        $chatMessage->setSentAt(new \DateTime());
        $chatMessage->setChat($chat);

        // Figure out which user to message from the chat object
        $userToMessage = $chat->getUserOne()
                              ->getId() === $loggedInUser->getId() ? $chat->getUserTwo() : $chat->getUserOne();
        $chatMessage->setSentTo($userToMessage);

        $share = new Share();
        $share->setSentFrom($loggedInUser);
        $share->setSentTo($userToMessage);

        if ($experience) {
            $share->setExperience($experience);
        }

        if ($requestEntity) {
            $share->setRequest($requestEntity);
        }

        $this->entityManager->persist($chatMessage);
        $this->entityManager->persist($share);
        $this->entityManager->flush();

        $this->experienceMailer->genericShareNotification($message, $user, $loggedInUser);

        return new JsonResponse([
                'success' => true,
                'message' => 'Notification sent!',
            ], Response::HTTP_OK);
    }
}
