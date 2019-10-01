<?php


namespace App\Util;


use App\Entity\CompanyExperience;
use App\Mailer\FeedbackMailer;
use App\Mailer\ImportMailer;
use App\Mailer\RecapMailer;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Mailer\UnseenMessagesMailer;
use App\Repository\AdminUserRepository;
use App\Repository\ChatMessageRepository;
use App\Repository\ChatRepository;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyFavoriteRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\EducatorRegisterStudentForExperienceRequestRepository;
use App\Repository\EducatorReviewCompanyExperienceFeedbackRepository;
use App\Repository\EducatorReviewTeachLessonExperienceFeedbackRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\ExperienceRepository;
use App\Repository\IndustryRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\RegionalCoordinatorRepository;
use App\Repository\RegistrationRepository;
use App\Repository\SchoolExperienceRepository;
use App\Repository\RequestRepository;
use App\Repository\SchoolAdministratorRepository;
use App\Repository\SchoolRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\SiteAdminUserRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StudentReviewCompanyExperienceFeedbackRepository;
use App\Repository\StudentReviewTeachLessonExperienceFeedbackRepository;
use App\Repository\StudentUserRepository;
use App\Repository\TeachLessonExperienceRepository;
use App\Repository\TeachLessonRequestRepository;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Knp\Component\Pager\Paginator;

trait ServiceHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var Packages
     */
    private $assetsManager;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var CompanyPhotoRepository
     */
    private $companyPhotoRepository;

    /**
     * @var AdminUserRepository
     */
    private $adminUserRepository;

    /**
     * @var RequestsMailer
     */
    private $requestsMailer;

    /**
     * @var SecurityMailer
     */
    private $securityMailer;

    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

    /**
     * @var JoinCompanyRequestRepository
     */
    private $joinCompanyRequestRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var LessonFavoriteRepository
     */
    private $lessonFavoriteRepository;

    /**
     * @var LessonTeachableRepository
     */
    private $lessonTeachableRepository;

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * @var EducatorUserRepository
     */
    private $educatorUserRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ImportMailer
     */
    private $importMailer;

    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    /**
     * @var IndustryRepository
     */
    private $industryRepository;

    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * @var RegionalCoordinatorRepository
     */
    private $regionalCoordinatorRepository;

    /**
     * @var TeachLessonRequestRepository
     */
    private $teachLessonRequestRepository;

    /**
     * @var SchoolExperienceRepository
     */
    private $schoolExperienceRepository;

    /**
     * @var RequestRepository
     */
    private $requestRepository;

    /**
     * @var ChatRepository;
     */
    private $chatRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var SiteAdminUserRepository
     */
    private $siteAdminRepository;

    /**
     * @var SchoolAdministratorRepository
     */
    private $schoolAdministratorRepository;

    /**
     * @var StateCoordinatorRepository
     */
    private $stateCoordinatorRepository;

    /**
     * @var FeedbackMailer
     */
    private $feedbackMailer;

    /**
     * @var MessageBusInterface $bus
     */
    private $bus;

    /**
     * @var LessonRepository
     */
    private $lessonRepository;

    /**
     * @var RecapMailer
     */
    private $recapMailer;

    /**
     * @var ExperienceRepository
     */
    private $experienceRepository;

    /**
     * @var CompanyExperienceRepository
     */
    private $companyExperienceRepository;

    /**
     * @var GuardAuthenticatorHandler $guardHandler,
     */
    private $guardHandler;

    /**
     * @var LoginFormAuthenticator $authenticator
     */
    private $authenticator;

    /**
     * @var ChatMessageRepository $chatMessageRepository
     */
    private $chatMessageRepository;

    /**
     * @var EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository
     */
    private $educatorRegisterStudentForExperienceRequestRepository;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var TeachLessonExperienceRepository
     */
    private $teachLessonExperienceRepository;

    /**
     * @var UnseenMessagesMailer
     */
    private $unseenMessagesMailer;

    /**
     * @var CompanyFavoriteRepository
     */
    private $companyFavoriteRepository;

    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var EducatorReviewCompanyExperienceFeedbackRepository
     */
    private $educatorReviewCompanyExperienceFeedbackRepository;

    /**
     * @var EducatorReviewTeachLessonExperienceFeedbackRepository
     */
    private $educatorReviewTeachLessonExperienceFeedbackRepository;

    /**
     * @var StudentReviewCompanyExperienceFeedbackRepository
     */
    private $studentReviewCompanyExperienceFeedbackRepository;

    /**
     * @var StudentReviewTeachLessonExperienceFeedbackRepository
     */
    private $studentReviewTeachLessonExperienceFeedbackRepository;

    /**
     * ServiceHelper constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param AdminUserRepository $adminUserRepository
     * @param RequestsMailer $requestsMailer
     * @param SecurityMailer $securityMailer
     * @param ProfessionalUserRepository $professionalUserRepository
     * @param JoinCompanyRequestRepository $joinCompanyRequestRepository
     * @param UserRepository $userRepository
     * @param CacheManager $cacheManager
     * @param RouterInterface $router
     * @param LessonFavoriteRepository $lessonFavoriteRepository
     * @param LessonTeachableRepository $lessonTeachableRepository
     * @param StudentUserRepository $studentUserRepository
     * @param EducatorUserRepository $educatorUserRepository
     * @param SerializerInterface $serializer
     * @param ImportMailer $importMailer
     * @param ValidatorInterface $validator
     * @param IndustryRepository $industryRepository
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     * @param RegionalCoordinatorRepository $regionalCoordinatorRepository
     * @param TeachLessonRequestRepository $teachLessonRequestRepository
     * @param SchoolExperienceRepository $schoolExperienceRepository
     * @param RequestRepository $requestRepository
     * @param ChatRepository $chatRepository
     * @param PaginatorInterface $paginator
     * @param SiteAdminUserRepository $siteAdminRepository
     * @param SchoolAdministratorRepository $schoolAdministratorRepository
     * @param StateCoordinatorRepository $stateCoordinatorRepository
     * @param FeedbackMailer $feedbackMailer
     * @param MessageBusInterface $bus
     * @param LessonRepository $lessonRepository
     * @param RecapMailer $recapMailer
     * @param ExperienceRepository $experienceRepository
     * @param CompanyExperienceRepository $companyExperienceRepository
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @param ChatMessageRepository $chatMessageRepository
     * @param EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository
     * @param SchoolRepository $schoolRepository
     * @param TeachLessonExperienceRepository $teachLessonExperienceRepository
     * @param UnseenMessagesMailer $unseenMessagesMailer
     * @param CompanyFavoriteRepository $companyFavoriteRepository
     * @param RegistrationRepository $registrationRepository
     * @param EducatorReviewCompanyExperienceFeedbackRepository $educatorReviewCompanyExperienceFeedbackRepository
     * @param EducatorReviewTeachLessonExperienceFeedbackRepository $educatorReviewTeachLessonExperienceFeedbackRepository
     * @param StudentReviewCompanyExperienceFeedbackRepository $studentReviewCompanyExperienceFeedbackRepository
     * @param StudentReviewTeachLessonExperienceFeedbackRepository $studentReviewTeachLessonExperienceFeedbackRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        Packages $assetsManager,
        CompanyRepository $companyRepository,
        CompanyPhotoRepository $companyPhotoRepository,
        AdminUserRepository $adminUserRepository,
        RequestsMailer $requestsMailer,
        SecurityMailer $securityMailer,
        ProfessionalUserRepository $professionalUserRepository,
        JoinCompanyRequestRepository $joinCompanyRequestRepository,
        UserRepository $userRepository,
        CacheManager $cacheManager,
        RouterInterface $router,
        LessonFavoriteRepository $lessonFavoriteRepository,
        LessonTeachableRepository $lessonTeachableRepository,
        StudentUserRepository $studentUserRepository,
        EducatorUserRepository $educatorUserRepository,
        SerializerInterface $serializer,
        ImportMailer $importMailer,
        ValidatorInterface $validator,
        IndustryRepository $industryRepository,
        SecondaryIndustryRepository $secondaryIndustryRepository,
        RegionalCoordinatorRepository $regionalCoordinatorRepository,
        TeachLessonRequestRepository $teachLessonRequestRepository,
        SchoolExperienceRepository $schoolExperienceRepository,
        RequestRepository $requestRepository,
        ChatRepository $chatRepository,
        PaginatorInterface $paginator,
        SiteAdminUserRepository $siteAdminRepository,
        SchoolAdministratorRepository $schoolAdministratorRepository,
        StateCoordinatorRepository $stateCoordinatorRepository,
        FeedbackMailer $feedbackMailer,
        MessageBusInterface $bus,
        LessonRepository $lessonRepository,
        RecapMailer $recapMailer,
        ExperienceRepository $experienceRepository,
        CompanyExperienceRepository $companyExperienceRepository,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        ChatMessageRepository $chatMessageRepository,
        EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository,
        SchoolRepository $schoolRepository,
        TeachLessonExperienceRepository $teachLessonExperienceRepository,
        UnseenMessagesMailer $unseenMessagesMailer,
        CompanyFavoriteRepository $companyFavoriteRepository,
        RegistrationRepository $registrationRepository,
        EducatorReviewCompanyExperienceFeedbackRepository $educatorReviewCompanyExperienceFeedbackRepository,
        EducatorReviewTeachLessonExperienceFeedbackRepository $educatorReviewTeachLessonExperienceFeedbackRepository,
        StudentReviewCompanyExperienceFeedbackRepository $studentReviewCompanyExperienceFeedbackRepository,
        StudentReviewTeachLessonExperienceFeedbackRepository $studentReviewTeachLessonExperienceFeedbackRepository
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
        $this->adminUserRepository = $adminUserRepository;
        $this->requestsMailer = $requestsMailer;
        $this->securityMailer = $securityMailer;
        $this->professionalUserRepository = $professionalUserRepository;
        $this->joinCompanyRequestRepository = $joinCompanyRequestRepository;
        $this->userRepository = $userRepository;
        $this->cacheManager = $cacheManager;
        $this->router = $router;
        $this->lessonFavoriteRepository = $lessonFavoriteRepository;
        $this->lessonTeachableRepository = $lessonTeachableRepository;
        $this->studentUserRepository = $studentUserRepository;
        $this->educatorUserRepository = $educatorUserRepository;
        $this->serializer = $serializer;
        $this->importMailer = $importMailer;
        $this->validator = $validator;
        $this->industryRepository = $industryRepository;
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
        $this->regionalCoordinatorRepository = $regionalCoordinatorRepository;
        $this->teachLessonRequestRepository = $teachLessonRequestRepository;
        $this->schoolExperienceRepository = $schoolExperienceRepository;
        $this->requestRepository = $requestRepository;
        $this->chatRepository = $chatRepository;
        $this->paginator = $paginator;
        $this->siteAdminRepository = $siteAdminRepository;
        $this->schoolAdministratorRepository = $schoolAdministratorRepository;
        $this->stateCoordinatorRepository = $stateCoordinatorRepository;
        $this->feedbackMailer = $feedbackMailer;
        $this->bus = $bus;
        $this->lessonRepository = $lessonRepository;
        $this->recapMailer = $recapMailer;
        $this->experienceRepository = $experienceRepository;
        $this->companyExperienceRepository = $companyExperienceRepository;
        $this->guardHandler = $guardHandler;
        $this->authenticator = $authenticator;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->educatorRegisterStudentForExperienceRequestRepository = $educatorRegisterStudentForExperienceRequestRepository;
        $this->schoolRepository = $schoolRepository;
        $this->teachLessonExperienceRepository = $teachLessonExperienceRepository;
        $this->unseenMessagesMailer = $unseenMessagesMailer;
        $this->companyFavoriteRepository = $companyFavoriteRepository;
        $this->registrationRepository = $registrationRepository;
        $this->educatorReviewCompanyExperienceFeedbackRepository = $educatorReviewCompanyExperienceFeedbackRepository;
        $this->educatorReviewTeachLessonExperienceFeedbackRepository = $educatorReviewTeachLessonExperienceFeedbackRepository;
        $this->studentReviewCompanyExperienceFeedbackRepository = $studentReviewCompanyExperienceFeedbackRepository;
        $this->studentReviewTeachLessonExperienceFeedbackRepository = $studentReviewTeachLessonExperienceFeedbackRepository;
    }

    public function getFullQualifiedBaseUrl() {
        $routerContext = $this->router->getContext();
        $port = $routerContext->getHttpPort();
        return sprintf('%s://%s%s%s',
            $routerContext->getScheme(),
            $routerContext->getHost(),
            ($port !== 80 ? ':'. $port : ''),
            $routerContext->getBaseUrl()
        );
    }
}