<?php


namespace App\Util;


use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\AdminUserRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\StudentUserRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

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
        SerializerInterface $serializer
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