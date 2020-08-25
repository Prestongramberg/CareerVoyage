<?php


namespace App\Twig;

use App\Entity\CompanyResource;
use App\Entity\Company;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\JoinCompanyRequest;
use App\Entity\Lesson;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\Site;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\StudentToMeetProfessionalRequest;
use App\Entity\StudentUser;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Entity\UserRegisterForSchoolExperienceRequest;
use App\Repository\EducatorRegisterStudentForExperienceRequestRepository;
use App\Repository\ChatMessageRepository;
use App\Repository\ChatRepository;
use App\Repository\RequestRepository;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use App\Security\ProfileVoter;
use App\Service\UploaderHelper;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
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
     * @var EducatorRegisterStudentForExperienceRequestRepository
     */
    private $educatorRegisterStudentForExperienceRequestRepository;

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
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Security
     */
    private $security;

    /**
     * AppExtension constructor.
     * @param UploaderHelper $uploadHelper
     * @param SerializerInterface $serializer
     * @param RequestRepository $requestRepository
     * @param UserRepository $userRepository
     * @param ChatRepository $chatRepository
     * @param ChatMessageRepository $chatMessageRepository
     * @param Environment $twig
     * @param SiteRepository $siteRepository
     * @param RouterInterface $router
     * @param Security $security
     */
    public function __construct(
        UploaderHelper $uploadHelper,
        SerializerInterface $serializer,
        RequestRepository $requestRepository,
        UserRepository $userRepository,
        ChatRepository $chatRepository,
        ChatMessageRepository $chatMessageRepository,
        Environment $twig,
        SiteRepository $siteRepository,
        RouterInterface $router,
        Security $security,
        EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository
    ) {
        $this->uploadHelper = $uploadHelper;
        $this->serializer = $serializer;
        $this->requestRepository = $requestRepository;
        $this->userRepository = $userRepository;
        $this->chatRepository = $chatRepository;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->twig = $twig;
        $this->siteRepository = $siteRepository;
        $this->router = $router;
        $this->security = $security;
        $this->educatorRegisterStudentForExperienceRequestRepository = $educatorRegisterStudentForExperienceRequestRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath']),
            new TwigFunction('encode_lesson', [$this, 'encodeLesson']),
            new TwigFunction('encode_company', [$this, 'encodeCompany']),
            new TwigFunction('encode_companies', [$this, 'encodeCompanies']),
            new TwigFunction('encode_school', [$this, 'encodeSchool']),
            new TwigFunction('encode_schools', [$this, 'encodeSchools']),
            new TwigFunction('encode_user', [$this, 'encodeUser']),
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
            new TwigFunction('list_pluck', [$this, 'listPluck']),
            new TwigFunction('quote_array_elements_for_react', [$this, 'quoteArrayElementsForReact']),
            new TwigFunction('get_site', [$this, 'getSite']),
            new TwigFunction('user_can_chat_with_user', [$this, 'userCanChatWithUser']),
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

	public function encodeCompanies($objects): string
	{
		return $this->serializer->serialize($objects, 'json', ['groups' => ['RESULTS_PAGE']]);
	}

	public function encodeSchool(School $object): string
	{
		return $this->serializer->serialize($object, 'json', ['groups' => ['RESULTS_PAGE']]);
	}

    public function encodeUser(User $object): string
    {
        return $this->serializer->serialize($object, 'json', ['groups' => ['ALL_USER_DATA']]);
    }

	public function encodeSchools($objects): string
	{
		return $this->serializer->serialize($objects, 'json', ['groups' => ['RESULTS_PAGE']]);
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

        $company_experience_total = 0;
        if($user->isStudent()) {
            $companyExperienceRequests = $this->educatorRegisterStudentForExperienceRequestRepository->getUnreadStudentCompanyRequests($user);
            $company_experience_total = count($companyExperienceRequests);
        }

        if($user->isEducator()) {
            $companyExperienceRequests = $this->educatorRegisterStudentForExperienceRequestRepository->getUnreadEducatorCompanyRequests($user);
            $company_experience_total = count($companyExperienceRequests);
        }

        $requests= $this->requestRepository->getRequestsThatNeedMyApproval($user);
        $requests_total = count($requests);
        return $company_experience_total + $requests_total;
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

    public function renderRequest( $request, $user, $location = "", $parentTab = "" ) {

        switch ($request->getClassName()) {
            case "JoinCompanyRequest":

                /** @var JoinCompanyRequest $request */
                if($this->containsNullObjects([
                    $request->getCompany(),
                    $request->getCreatedBy(),
                    $request->getNeedsApprovalBy()
                ])) {
                    return '';
                }

                return $this->twig->render('request/partials/_join_companies.html.twig', [
                    'request' => $request,
                    'user' => $user,
                    'location' => $location,
                    'parentTab' => $parentTab
                ]);
                break;
            case "NewCompanyRequest":
                /** @var NewCompanyRequest $request */
                if($this->containsNullObjects([
                    $request->getCompany(),
                    $request->getCreatedBy(),
                    $request->getNeedsApprovalBy()
                ])) {
                    return '';
                }
                return $this->twig->render('request/partials/_new_companies.html.twig', [
                    'request' => $request,
                    'user' => $user,
                    'location' => $location,
                    'parentTab' => $parentTab
                ]);
                break;
            case "TeachLessonRequest":
                /** @var TeachLessonRequest $request */
                if($this->containsNullObjects([
                    $request->getLesson(),
                    $request->getSchool(),
                    $request->getCreatedBy(),
                    $request->getNeedsApprovalBy()
                ])) {
                    return '';
                }
                return $this->twig->render('request/partials/_teach_lesson_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                    'location' => $location,
                    'parentTab' => $parentTab
                ]);
                break;
            case "EducatorRegisterStudentForCompanyExperienceRequest":
                /** @var EducatorRegisterStudentForCompanyExperienceRequest $request */
                if($this->containsNullObjects([
                    $request->getCompanyExperience(),
                    $request->getStudentUser(),
                    $request->getCreatedBy(),
                    $request->getNeedsApprovalBy()
                ])) {
                    return '';
                }

                if(!$request->getStudentUser()) {
                    return '';
                }

                return $this->twig->render('request/partials/_educator_register_student_for_company_experience_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                    'location' => $location,
                    'parentTab' => $parentTab
                ]);
                break;
            case "StudentToMeetProfessionalRequest":

                /** @var StudentToMeetProfessionalRequest $request */
                if($this->containsNullObjects([
                    $request->getStudent(),
                    $request->getProfessional(),
                    $request->getCreatedBy(),
                    $request->getNeedsApprovalBy(),
                    $request->getReasonToMeet()
                ])) {
                    return '';
                }

                return $this->twig->render('request/partials/_student_to_meet_professional_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                    'location' => $location,
                    'parentTab' => $parentTab
                ]);
                break;
            case "UserRegisterForSchoolExperienceRequest":

                /** @var UserRegisterForSchoolExperienceRequest $request */
                if($this->containsNullObjects([
                    $request->getSchoolExperience(),
                    $request->getUser(),
                    $request->getCreatedBy(),
                    $request->getNeedsApprovalBy()
                ])) {
                    return '';
                }

                return $this->twig->render('request/partials/_user_register_for_school_experience_request.html.twig', [
                    'request' => $request,
                    'user' => $user,
                    'location' => $location,
                    'parentTab' => $parentTab
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

    public function listPluck ( $list, $field, $index_key = null ) {
        if ( ! $index_key ) {
            /*
             * This is simple. Could at some point wrap array_column()
             * if we knew we had an array of arrays.
             */
            foreach ( $list as $key => $value ) {
                if ( is_object( $value ) ) {
                    $list[ $key ] = $value->$field;
                } else {
                    $list[ $key ] = $value[ $field ];
                }
            }
            return $list;
        }

        /*
         * When index_key is not set for a particular item, push the value
         * to the end of the stack. This is how array_column() behaves.
         */
        $newlist = array();
        foreach ( $list as $value ) {
            if ( is_object( $value ) ) {
                if ( isset( $value->$index_key ) ) {
                    $newlist[ $value->$index_key ] = $value->$field;
                } else {
                    $newlist[] = $value->$field;
                }
            } else {
                if ( isset( $value[ $index_key ] ) ) {
                    $newlist[ $value[ $index_key ] ] = $value[ $field ];
                } else {
                    $newlist[] = $value[ $field ];
                }
            }
        }

        return $newlist;
    }

    public function quoteArrayElementsForReact( $array ) {
        return array_map( function( $value ) {
            return '"' . $value . '"';
        }, $array );
    }

    /**
     * We are attempting to grab the site object from the user.
     *
     * Here's what's going on:
     * 1. We go ahead and try to get the site object from the logged in user depending on the user type.
     * 2. Admins, professionals, and non logged in users don't have a site object attached so in this case we look to the site url as the source of truth
     * 3. For some reason if we still aren't getting a site we don't want to break anything so just return an empty site object
     * @return Site|null
     */
    public function getSite() {

        $user = $this->security->getUser();

        if($user && ($user instanceof SiteAdminUser ||
            $user instanceof RegionalCoordinator ||
            $user instanceof StateCoordinator ||
            $user instanceof SchoolAdministrator ||
            $user instanceof EducatorUser ||
            $user instanceof StudentUser) ) {
            $site = $user->getSite();
        } else {

            $site = $this->siteRepository->findOneBy([
                'fullyQualifiedBaseUrl' => $this->getFullyQualifiedBaseUrl()
            ]);

            if(!$site) {
                $site = new Site();
            }
        }
        return $site;
    }

    public function userCanChatWithUser( User $user, User $userToBeChatted ) {

        // Users should not chat with themselves
        if( $user && $userToBeChatted && $user->getId() === $userToBeChatted->getId() ) {
            return false;
        }

        // Educators can chat with Educators, Professionals, and Students
        if($user && ($user instanceof EducatorUser) ) {
            if ( $userToBeChatted && ($userToBeChatted instanceof EducatorUser ||
                $userToBeChatted instanceof ProfessionalUser ||
                $userToBeChatted instanceof StudentUser ) ) {
                return true;
            }
        }

        // Students can chat with Students and Educators
        if($user && ($user instanceof StudentUser) ) {
            if ( $userToBeChatted && ($userToBeChatted instanceof StudentUser ||
                $userToBeChatted instanceof EducatorUser ) ) {
                return true;
            }
        }

        // Professionals can chat with Educators and Professionals
        if($user && ($user instanceof ProfessionalUser) ) {
            if ( $userToBeChatted && ($userToBeChatted instanceof EducatorUser ||
                $userToBeChatted instanceof ProfessionalUser ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate the fully qualified base URL (scheme + host + port, if not default + app base path)
     *
     * @return string
     */
    protected function getFullyQualifiedBaseUrl()
    {
        $routerContext = $this->router->getContext();
        $port = $routerContext->getHttpPort();

        return sprintf(
            '%s://%s%s%s',
            $routerContext->getScheme(),
            $routerContext->getHost(),
            ($port !== 80 ? ':'.$port : ''),
            $routerContext->getBaseUrl()
        );
    }

    /**
     * Some weird bugs where requests view showing errors for some values being null
     * This is more of a safety check incase that happens again in the future
     * @param $objs
     * @return bool
     */
    private function containsNullObjects($objs) {
        foreach($objs as $obj) {
            if(!$obj) {
                return true;
            }
        }
        return false;
    }
}
