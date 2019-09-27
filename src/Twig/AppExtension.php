<?php


namespace App\Twig;

use App\Entity\CompanyResource;
use App\Entity\Company;
use App\Entity\Experience;
use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\ChatMessageRepository;
use App\Repository\ChatRepository;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use App\Security\ProfileVoter;
use App\Service\UploaderHelper;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Environment;

class AppExtension extends AbstractExtension
{

    /**
     * @var UploaderHelper
     */
    private $uploadHelper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var RequestRepository
     */
    private $requestRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ChatRepository
     */
    private $chatRepository;

    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * AppExtension constructor.
     * @param UploaderHelper $uploadHelper
     * @param SerializerInterface $serializer
     * @param RequestRepository $requestRepository
     * @param UserRepository $userRepository
     * @param ChatRepository $chatRepository
     * @param ChatMessageRepository $chatMessageRepository
     * @param Environment $twig
     */
    public function __construct(UploaderHelper $uploadHelper, SerializerInterface $serializer, RequestRepository $requestRepository, UserRepository $userRepository, ChatRepository $chatRepository, ChatMessageRepository $chatMessageRepository, Environment $twig)
    {
        $this->uploadHelper = $uploadHelper;
        $this->serializer = $serializer;
        $this->requestRepository = $requestRepository;
        $this->userRepository = $userRepository;
        $this->chatRepository = $chatRepository;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->twig = $twig;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath']),
            new TwigFunction('encode_lesson', [$this, 'encodeLesson']),
            new TwigFunction('encode_company', [$this, 'encodeCompany']),
            new TwigFunction('encode_company_resources', [$this, 'encodeCompanyResources']),
            new TwigFunction('pending_requests', [$this, 'pendingRequests']),
            new TwigFunction('encode_secondary_industries', [$this, 'encodeSecondaryIndustries']),
            new TwigFunction('validate_url', [$this, 'validateUrl']),
            new TwigFunction('excerpt_length', [$this, 'excerptLength']),
            new TwigFunction('ucwords', [$this, 'ucwords']),
            new TwigFunction('user_can_edit_user', [$this, 'userCanEditUser']),
            new TwigFunction('get_env', [$this, 'getEnv']),
            new TwigFunction('unread_messages', [$this, 'unreadMessages']),
            new TwigFunction('render_request', [$this, 'renderRequest']),
            new TwigFunction('render_request_status_text', [$this, 'renderRequestStatusText']),
        ];
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->uploadHelper->getPublicPath($path);
    }

    public function encodeLesson(Lesson $object): string
    {
        return $this->serializer->serialize($object, 'json', ['groups' => ['LESSON_DATA']]);
    }

    public function encodeCompany(Company $object): string
    {
        return $this->serializer->serialize($object, 'json', ['groups' => ['RESULTS_PAGE']]);
    }

    public function encodeCompanyResources($companyResources): string
    {
        return $this->serializer->serialize($companyResources, 'json', ['groups' => ['COMPANY_RESOURCE']]);
    }

    public function encodeSecondaryIndustries($secondaryIndustries): string
    {
        return $this->serializer->serialize($secondaryIndustries, 'json', ['groups' => ['RESULTS_PAGE']]);
    }

    public function validateUrl( $url ) : string
    {
        $parsed = parse_url($url);
        if (empty($parsed['scheme'])) {
            $url = 'http://' . ltrim($url, '/');
        }
        return $url;
    }

    public function excerptLength( $content, $limit = 200 )
    {
        if(!$content) {
            return '';
        }

        if ( strlen( $content ) <= $limit) {
            return $content;
        } else {
            return substr( $content, 0, $limit) . '...';
        }
    }

    public function pendingRequests(User $user) {

        $requests = $this->requestRepository->findBy([
            'needsApprovalBy' => $user,
            'approved' => false
        ]);

        return count($requests);
    }

    public function ucwords($text) {
        return $this->ucwords($text);
    }

    public function userCanEditUser( User $user, User $userToVoteOn ) {
        return ProfileVoter::canEdit( $userToVoteOn, $user );
    }

    public function getEnv( $variable ) {
        return $_ENV[ $variable ];
    }

    public function unreadMessages( $userId ) {
        $user = $this->userRepository->find($userId);
        $unreadMessages = $this->chatMessageRepository->findBy(['sentTo' => $user,'hasBeenRead' => false]);
        return count($unreadMessages);
    }

    public function renderRequest( $request, $user ) {

        switch ($request->getClassName()) {
            case "JoinCompanyRequest":
                return $this->twig->render('request/partials/_join_companies.html.twig', [
                    'request' => $request,
                    'user' => $user,
                ]);
                break;
            case "NewCompanyRequest":
                return $this->twig->render('request/partials/_new_companies.html.twig', [
                    'request' => $request,
                    'user' => $user,
                ]);
                break;
            case "StateCoordinatorRequest":
                return $this->twig->render('request/partials/_state_coordinator_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                ]);
                break;
            case "RegionalCoordinatorRequest":
                return $this->twig->render('request/partials/_regional_coordinator_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                ]);
                break;
            case "SchoolAdministratorRequest":
                return $this->twig->render('request/partials/_school_administrator_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                ]);
                break;
            case "TeachLessonRequest":
                return $this->twig->render('request/partials/_teach_lesson_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                ]);
                break;
            case "EducatorRegisterStudentForCompanyExperienceRequest":
                return $this->twig->render('request/partials/_educator_register_student_for_company_experience_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                ]);
                break;
            default:
                return null;
        }

    }

    public function renderRequestStatusText( $request ) {

        if ( $request->getApproved() === true ) {
            return "Approved";
        }

        if ( $request->getDenied() === true ) {
            return "Denied";
        }

        return "Pending";
    }
}
