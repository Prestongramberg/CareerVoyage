<?php

namespace App\Util;

use App\Mailer\FeedbackMailer;
use App\Mailer\ImportMailer;
use App\Mailer\RecapMailer;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Mailer\ExperienceMailer;
use App\Repository\AdminUserRepository;
use App\Repository\AllowedCommunicationRepository;
use App\Repository\CareerVideoRepository;
use App\Repository\ChatMessageRepository;
use App\Repository\ChatRepository;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyFavoriteRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\CompanyVideoRepository;
use App\Repository\CountyRepository;
use App\Repository\EducatorRegisterStudentForExperienceRequestRepository;
use App\Repository\EducatorRegisterEducatorForCompanyExperienceRequestRepository;
use App\Repository\EducatorReviewCompanyExperienceFeedbackRepository;
use App\Repository\EducatorReviewTeachLessonExperienceFeedbackRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\ExperienceRepository;
use App\Repository\FeedbackRepository;
use App\Repository\HelpVideoRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\ProfessionalVideoRepository;
use App\Repository\RegionalCoordinatorRepository;
use App\Repository\RegionRepository;
use App\Repository\RegistrationRepository;
use App\Repository\ReportColumnRepository;
use App\Repository\ReportGroupRepository;
use App\Repository\ReportRepository;
use App\Repository\RolesWillingToFulfillRepository;
use App\Repository\SchoolExperienceRepository;
use App\Repository\RequestRepository;
use App\Repository\SchoolAdministratorRepository;
use App\Repository\SchoolAdminRegisterSAForCompanyExperienceRequestRepository;
use App\Repository\SchoolRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\SiteAdminUserRepository;
use App\Repository\SiteRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StudentReviewCompanyExperienceFeedbackRepository;
use App\Repository\StudentReviewTeachLessonExperienceFeedbackRepository;
use App\Repository\StudentUserRepository;
use App\Repository\SystemUserRepository;
use App\Repository\TeachLessonExperienceRepository;
use App\Repository\TeachLessonRequestRepository;
use App\Repository\UserMetaRepository;
use App\Repository\UserRegisterForSchoolExperienceRequestRepository;
use App\Repository\UserRepository;
use App\Repository\VideoFavoriteRepository;
use App\Repository\VideoRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\ChatHelper;
use App\Service\FileUploader;
use App\Service\Geocoder;
use App\Service\GlobalShare;
use App\Service\ImageCacheGenerator;
use App\Service\PhpSpreadsheetHelper;
use App\Service\ReportService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Limenius\Liform\Liform;

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
     * @var ExperienceMailer
     */
    private $experienceMailer;

    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

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
     * @var GuardAuthenticatorHandler $guardHandler ,
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
     * @var EducatorRegisterEducatorForCompanyExperienceRequestRepository $educatorRegisterEducatorForCompanyExperienceRequestRepository
     */
    private $educatorRegisterEducatorForCompanyExperienceRequestRepository;

    /**
     * @var SchoolAdminRegisterSAForCompanyExperienceRequestRepository $schoolAdminRegisterSAForCompanyExperienceRequestRepository
     */
    private $schoolAdminRegisterSAForCompanyExperienceRequestRepository;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var TeachLessonExperienceRepository
     */
    private $teachLessonExperienceRepository;

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
     * @var FeedbackRepository
     */
    private $feedbackRepository;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * @var TokenStorageInterface
     */
    private $securityToken;

    /**
     * @var FilterBuilderUpdaterInterface
     */
    private $filterBuilder;

    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * @var RolesWillingToFulfillRepository
     */
    private $rolesWillingToFulfillRepository;

    /**
     * @var AllowedCommunicationRepository
     */
    private $allowedCommunicationsRepository;

    /**
     * @var UserRegisterForSchoolExperienceRequestRepository
     */
    private $userRegisterForSchoolExperienceRequestRepository;

    /**
     * @var PhpSpreadsheetHelper;
     */
    private $phpSpreadsheetHelper;

    /**
     * @var CompanyVideoRepository
     */
    private $companyVideoRepository;

    /**
     * @var CareerVideoRepository
     */
    private $careerVideoRepository;

    /**
     * @var VideoFavoriteRepository
     */
    private $videoFavoriteRepository;

    /**
     * @var VideoRepository
     */
    private $videoRepository;

    /**
     * @var ProfessionalVideoRepository
     */
    private $professionalVideoRepository;

    /**
     * @var HelpVideoRepository
     */
    private $helpVideoRepository;

    /**
     * @var ChatHelper
     */
    private $chatHelper;

    /**
     * @var GlobalShare
     */
    private $globalShare;

    /**
     * @var SystemUserRepository
     */
    private $systemUserRepository;

    /**
     * @var RegionRepository
     */
    private $regionRepository;

    /**
     * @var Liform
     */
    private $liform;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var ReportColumnRepository
     */
    private $reportColumnRepository;

    /**
     * @var CountyRepository
     */
    private $countyRepository;

    /**
     * @var ReportService
     */
    private $reportService;

    /**
     * @var ReportGroupRepository
     */
    private $reportGroupRepository;

    /**
     * @var UserMetaRepository
     */
    private $userMetaRepository;

    /**
     * ServiceHelper constructor.
     *
     * @param EntityManagerInterface                                        $entityManager
     * @param FileUploader                                                  $fileUploader
     * @param UserPasswordEncoderInterface                                  $passwordEncoder
     * @param ImageCacheGenerator                                           $imageCacheGenerator
     * @param UploaderHelper                                                $uploaderHelper
     * @param Packages                                                      $assetsManager
     * @param CompanyRepository                                             $companyRepository
     * @param CompanyPhotoRepository                                        $companyPhotoRepository
     * @param AdminUserRepository                                           $adminUserRepository
     * @param RequestsMailer                                                $requestsMailer
     * @param SecurityMailer                                                $securityMailer
     * @param ExperienceMailer                                              $experienceMailer
     * @param ProfessionalUserRepository                                    $professionalUserRepository
     * @param UserRepository                                                $userRepository
     * @param CacheManager                                                  $cacheManager
     * @param RouterInterface                                               $router
     * @param LessonFavoriteRepository                                      $lessonFavoriteRepository
     * @param LessonTeachableRepository                                     $lessonTeachableRepository
     * @param StudentUserRepository                                         $studentUserRepository
     * @param EducatorUserRepository                                        $educatorUserRepository
     * @param SerializerInterface                                           $serializer
     * @param ImportMailer                                                  $importMailer
     * @param ValidatorInterface                                            $validator
     * @param IndustryRepository                                            $industryRepository
     * @param SecondaryIndustryRepository                                   $secondaryIndustryRepository
     * @param RegionalCoordinatorRepository                                 $regionalCoordinatorRepository
     * @param TeachLessonRequestRepository                                  $teachLessonRequestRepository
     * @param SchoolExperienceRepository                                    $schoolExperienceRepository
     * @param RequestRepository                                             $requestRepository
     * @param ChatRepository                                                $chatRepository
     * @param PaginatorInterface                                            $paginator
     * @param SiteAdminUserRepository                                       $siteAdminRepository
     * @param SchoolAdministratorRepository                                 $schoolAdministratorRepository
     * @param StateCoordinatorRepository                                    $stateCoordinatorRepository
     * @param FeedbackMailer                                                $feedbackMailer
     * @param MessageBusInterface                                           $bus
     * @param LessonRepository                                              $lessonRepository
     * @param RecapMailer                                                   $recapMailer
     * @param ExperienceRepository                                          $experienceRepository
     * @param CompanyExperienceRepository                                   $companyExperienceRepository
     * @param GuardAuthenticatorHandler                                     $guardHandler
     * @param LoginFormAuthenticator                                        $authenticator
     * @param ChatMessageRepository                                         $chatMessageRepository
     * @param EducatorRegisterStudentForExperienceRequestRepository         $educatorRegisterStudentForExperienceRequestRepository
     * @param EducatorRegisterEducatorForCompanyExperienceRequestRepository $educatorRegisterEducatorForCompanyExperienceRequestRepository
     * @param SchoolAdminRegisterSAForCompanyExperienceRequestRepository    $schoolAdminRegisterSAForCompanyExperienceRequestRepository
     * @param SchoolRepository                                              $schoolRepository
     * @param TeachLessonExperienceRepository                               $teachLessonExperienceRepository
     * @param CompanyFavoriteRepository                                     $companyFavoriteRepository
     * @param RegistrationRepository                                        $registrationRepository
     * @param EducatorReviewCompanyExperienceFeedbackRepository             $educatorReviewCompanyExperienceFeedbackRepository
     * @param EducatorReviewTeachLessonExperienceFeedbackRepository         $educatorReviewTeachLessonExperienceFeedbackRepository
     * @param StudentReviewCompanyExperienceFeedbackRepository              $studentReviewCompanyExperienceFeedbackRepository
     * @param StudentReviewTeachLessonExperienceFeedbackRepository          $studentReviewTeachLessonExperienceFeedbackRepository
     * @param FeedbackRepository                                            $feedbackRepository
     * @param Environment                                                   $twig
     * @param SiteRepository                                                $siteRepository
     * @param TokenStorageInterface                                         $securityToken
     * @param FilterBuilderUpdaterInterface                                 $filterBuilder
     * @param Geocoder                                                      $geocoder
     * @param RolesWillingToFulfillRepository                               $rolesWillingToFulfillRepository
     * @param AllowedCommunicationRepository                                $allowedCommunicationsRepository
     * @param UserRegisterForSchoolExperienceRequestRepository              $userRegisterForSchoolExperienceRequestRepository
     * @param PhpSpreadsheetHelper                                          $phpSpreadsheetHelper
     * @param CompanyVideoRepository                                        $companyVideoRepository
     * @param CareerVideoRepository                                         $careerVideoRepository
     * @param VideoFavoriteRepository                                       $videoFavoriteRepository
     * @param VideoRepository                                               $videoRepository
     * @param ProfessionalVideoRepository                                   $professionalVideoRepository
     * @param HelpVideoRepository                                           $helpVideoRepository
     * @param ChatHelper                                                    $chatHelper
     * @param GlobalShare                                                   $globalShare
     * @param SystemUserRepository                                          $systemUserRepository
     * @param RegionRepository                                              $regionRepository
     * @param Liform                                                        $liform
     * @param ReportRepository                                              $reportRepository
     * @param ReportColumnRepository                                        $reportColumnRepository
     * @param CountyRepository                                              $countyRepository
     * @param ReportService                                                 $reportService
     * @param ReportGroupRepository                                         $reportGroupRepository
     * @param UserMetaRepository                                            $userMetaRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader,
                                UserPasswordEncoderInterface $passwordEncoder, ImageCacheGenerator $imageCacheGenerator,
                                UploaderHelper $uploaderHelper, Packages $assetsManager,
                                CompanyRepository $companyRepository, CompanyPhotoRepository $companyPhotoRepository,
                                AdminUserRepository $adminUserRepository, RequestsMailer $requestsMailer,
                                SecurityMailer $securityMailer, ExperienceMailer $experienceMailer,
                                ProfessionalUserRepository $professionalUserRepository, UserRepository $userRepository,
                                CacheManager $cacheManager, RouterInterface $router,
                                LessonFavoriteRepository $lessonFavoriteRepository,
                                LessonTeachableRepository $lessonTeachableRepository,
                                StudentUserRepository $studentUserRepository,
                                EducatorUserRepository $educatorUserRepository, SerializerInterface $serializer,
                                ImportMailer $importMailer, ValidatorInterface $validator,
                                IndustryRepository $industryRepository,
                                SecondaryIndustryRepository $secondaryIndustryRepository,
                                RegionalCoordinatorRepository $regionalCoordinatorRepository,
                                TeachLessonRequestRepository $teachLessonRequestRepository,
                                SchoolExperienceRepository $schoolExperienceRepository,
                                RequestRepository $requestRepository, ChatRepository $chatRepository,
                                PaginatorInterface $paginator, SiteAdminUserRepository $siteAdminRepository,
                                SchoolAdministratorRepository $schoolAdministratorRepository,
                                StateCoordinatorRepository $stateCoordinatorRepository, FeedbackMailer $feedbackMailer,
                                MessageBusInterface $bus, LessonRepository $lessonRepository, RecapMailer $recapMailer,
                                ExperienceRepository $experienceRepository,
                                CompanyExperienceRepository $companyExperienceRepository,
                                GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator,
                                ChatMessageRepository $chatMessageRepository,
                                EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository,
                                EducatorRegisterEducatorForCompanyExperienceRequestRepository $educatorRegisterEducatorForCompanyExperienceRequestRepository,
                                SchoolAdminRegisterSAForCompanyExperienceRequestRepository $schoolAdminRegisterSAForCompanyExperienceRequestRepository,
                                SchoolRepository $schoolRepository,
                                TeachLessonExperienceRepository $teachLessonExperienceRepository,
                                CompanyFavoriteRepository $companyFavoriteRepository,
                                RegistrationRepository $registrationRepository,
                                EducatorReviewCompanyExperienceFeedbackRepository $educatorReviewCompanyExperienceFeedbackRepository,
                                EducatorReviewTeachLessonExperienceFeedbackRepository $educatorReviewTeachLessonExperienceFeedbackRepository,
                                StudentReviewCompanyExperienceFeedbackRepository $studentReviewCompanyExperienceFeedbackRepository,
                                StudentReviewTeachLessonExperienceFeedbackRepository $studentReviewTeachLessonExperienceFeedbackRepository,
                                FeedbackRepository $feedbackRepository, Environment $twig,
                                SiteRepository $siteRepository, TokenStorageInterface $securityToken,
                                FilterBuilderUpdaterInterface $filterBuilder, Geocoder $geocoder,
                                RolesWillingToFulfillRepository $rolesWillingToFulfillRepository,
                                AllowedCommunicationRepository $allowedCommunicationsRepository,
                                UserRegisterForSchoolExperienceRequestRepository $userRegisterForSchoolExperienceRequestRepository,
                                PhpSpreadsheetHelper $phpSpreadsheetHelper,
                                CompanyVideoRepository $companyVideoRepository,
                                CareerVideoRepository $careerVideoRepository,
                                VideoFavoriteRepository $videoFavoriteRepository, VideoRepository $videoRepository,
                                ProfessionalVideoRepository $professionalVideoRepository,
                                HelpVideoRepository $helpVideoRepository, ChatHelper $chatHelper,
                                GlobalShare $globalShare, SystemUserRepository $systemUserRepository,
                                RegionRepository $regionRepository, Liform $liform, ReportRepository $reportRepository,
                                ReportColumnRepository $reportColumnRepository, CountyRepository $countyRepository,
                                ReportService $reportService, ReportGroupRepository $reportGroupRepository,
                                UserMetaRepository $userMetaRepository
    ) {
        $this->entityManager                                                 = $entityManager;
        $this->fileUploader                                                  = $fileUploader;
        $this->passwordEncoder                                               = $passwordEncoder;
        $this->imageCacheGenerator                                           = $imageCacheGenerator;
        $this->uploaderHelper                                                = $uploaderHelper;
        $this->assetsManager                                                 = $assetsManager;
        $this->companyRepository                                             = $companyRepository;
        $this->companyPhotoRepository                                        = $companyPhotoRepository;
        $this->adminUserRepository                                           = $adminUserRepository;
        $this->requestsMailer                                                = $requestsMailer;
        $this->securityMailer                                                = $securityMailer;
        $this->experienceMailer                                              = $experienceMailer;
        $this->professionalUserRepository                                    = $professionalUserRepository;
        $this->userRepository                                                = $userRepository;
        $this->cacheManager                                                  = $cacheManager;
        $this->router                                                        = $router;
        $this->lessonFavoriteRepository                                      = $lessonFavoriteRepository;
        $this->lessonTeachableRepository                                     = $lessonTeachableRepository;
        $this->studentUserRepository                                         = $studentUserRepository;
        $this->educatorUserRepository                                        = $educatorUserRepository;
        $this->serializer                                                    = $serializer;
        $this->importMailer                                                  = $importMailer;
        $this->validator                                                     = $validator;
        $this->industryRepository                                            = $industryRepository;
        $this->secondaryIndustryRepository                                   = $secondaryIndustryRepository;
        $this->regionalCoordinatorRepository                                 = $regionalCoordinatorRepository;
        $this->teachLessonRequestRepository                                  = $teachLessonRequestRepository;
        $this->schoolExperienceRepository                                    = $schoolExperienceRepository;
        $this->requestRepository                                             = $requestRepository;
        $this->chatRepository                                                = $chatRepository;
        $this->paginator                                                     = $paginator;
        $this->siteAdminRepository                                           = $siteAdminRepository;
        $this->schoolAdministratorRepository                                 = $schoolAdministratorRepository;
        $this->stateCoordinatorRepository                                    = $stateCoordinatorRepository;
        $this->feedbackMailer                                                = $feedbackMailer;
        $this->bus                                                           = $bus;
        $this->lessonRepository                                              = $lessonRepository;
        $this->recapMailer                                                   = $recapMailer;
        $this->experienceRepository                                          = $experienceRepository;
        $this->companyExperienceRepository                                   = $companyExperienceRepository;
        $this->guardHandler                                                  = $guardHandler;
        $this->authenticator                                                 = $authenticator;
        $this->chatMessageRepository                                         = $chatMessageRepository;
        $this->educatorRegisterStudentForExperienceRequestRepository         = $educatorRegisterStudentForExperienceRequestRepository;
        $this->educatorRegisterEducatorForCompanyExperienceRequestRepository = $educatorRegisterEducatorForCompanyExperienceRequestRepository;
        $this->schoolAdminRegisterSAForCompanyExperienceRequestRepository    = $schoolAdminRegisterSAForCompanyExperienceRequestRepository;
        $this->schoolRepository                                              = $schoolRepository;
        $this->teachLessonExperienceRepository                               = $teachLessonExperienceRepository;
        $this->companyFavoriteRepository                                     = $companyFavoriteRepository;
        $this->registrationRepository                                        = $registrationRepository;
        $this->educatorReviewCompanyExperienceFeedbackRepository             = $educatorReviewCompanyExperienceFeedbackRepository;
        $this->educatorReviewTeachLessonExperienceFeedbackRepository         = $educatorReviewTeachLessonExperienceFeedbackRepository;
        $this->studentReviewCompanyExperienceFeedbackRepository              = $studentReviewCompanyExperienceFeedbackRepository;
        $this->studentReviewTeachLessonExperienceFeedbackRepository          = $studentReviewTeachLessonExperienceFeedbackRepository;
        $this->feedbackRepository                                            = $feedbackRepository;
        $this->twig                                                          = $twig;
        $this->siteRepository                                                = $siteRepository;
        $this->securityToken                                                 = $securityToken;
        $this->filterBuilder                                                 = $filterBuilder;
        $this->geocoder                                                      = $geocoder;
        $this->rolesWillingToFulfillRepository                               = $rolesWillingToFulfillRepository;
        $this->allowedCommunicationsRepository                               = $allowedCommunicationsRepository;
        $this->userRegisterForSchoolExperienceRequestRepository              = $userRegisterForSchoolExperienceRequestRepository;
        $this->phpSpreadsheetHelper                                          = $phpSpreadsheetHelper;
        $this->companyVideoRepository                                        = $companyVideoRepository;
        $this->careerVideoRepository                                         = $careerVideoRepository;
        $this->videoFavoriteRepository                                       = $videoFavoriteRepository;
        $this->videoRepository                                               = $videoRepository;
        $this->professionalVideoRepository                                   = $professionalVideoRepository;
        $this->helpVideoRepository                                           = $helpVideoRepository;
        $this->chatHelper                                                    = $chatHelper;
        $this->globalShare                                                   = $globalShare;
        $this->systemUserRepository                                          = $systemUserRepository;
        $this->regionRepository                                              = $regionRepository;
        $this->liform                                                        = $liform;
        $this->reportRepository                                              = $reportRepository;
        $this->reportColumnRepository                                        = $reportColumnRepository;
        $this->countyRepository                                              = $countyRepository;
        $this->reportService                                                 = $reportService;
        $this->reportGroupRepository                                         = $reportGroupRepository;
        $this->userMetaRepository                                            = $userMetaRepository;
    }

    public function getFullQualifiedBaseUrl()
    {
        $routerContext = $this->router->getContext();
        $port          = $routerContext->getHttpPort();

        return sprintf(
            '%s://%s%s%s',
            $routerContext->getScheme(),
            $routerContext->getHost(),
            ($port !== 80 ? ':' . $port : ''),
            $routerContext->getBaseUrl()
        );
    }

}
