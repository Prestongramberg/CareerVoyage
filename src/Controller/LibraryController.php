<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Mailer\MyRequests\NewCompanyApprovedMailer;
use App\Mailer\RequestsThatNeedMyApproval\NewCompanyNeedsApprovalMailer;
use App\Repository\AdminUserRepository;
use App\Repository\CompanyFavoriteRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\File;
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
 * Class LibraryController
 * @package App\Controller
 * @Route("/dashboard")
 */
class LibraryController extends AbstractController
{
    use FileHelper;

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
     * @var NewCompanyNeedsApprovalMailer
     */
    private $newCompanyNeedsApprovalMailer;

    /**
     * @var CompanyFavoriteRepository
     */
    private $companyFavoriteRepository;

    /**
     * @var LessonFavoriteRepository
     */
    private $lessonFavoriteRepository;

    /**
     * LibraryController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param AdminUserRepository $adminUserRepository
     * @param NewCompanyNeedsApprovalMailer $newCompanyNeedsApprovalMailer
     * @param CompanyFavoriteRepository $companyFavoriteRepository
     * @param LessonFavoriteRepository $lessonFavoriteRepository
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
        NewCompanyNeedsApprovalMailer $newCompanyNeedsApprovalMailer,
        CompanyFavoriteRepository $companyFavoriteRepository,
        LessonFavoriteRepository $lessonFavoriteRepository
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
        $this->newCompanyNeedsApprovalMailer = $newCompanyNeedsApprovalMailer;
        $this->companyFavoriteRepository = $companyFavoriteRepository;
        $this->lessonFavoriteRepository = $lessonFavoriteRepository;
    }

    /**
     * @Route("/library", name="library", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $favoriteCompanies = $this->companyFavoriteRepository->findBy(
            [
                'user' => $this->getUser()
            ]
        );

        $favoriteLessons = $this->lessonFavoriteRepository->findBy(
            [
                'user' => $this->getUser()
            ]
        );

        $user = $this->getUser();

        return $this->render('library/index.html.twig', [
            'user' => $user,
            'favoriteCompanies' => $favoriteCompanies,
            'favoriteLessons' => $favoriteLessons,
        ]);
    }
}