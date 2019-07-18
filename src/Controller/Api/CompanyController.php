<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyDocument;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyVideo;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Repository\CompanyRepository;
use App\Repository\IndustryRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
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
 * Class CompanyController
 * @package App\Controller
 * @Route("/api")
 */
class CompanyController extends AbstractController
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
     * @var SerializerInterface
     */
    private $serializer;


    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var IndustryRepository
     */
    private $industryRepository;

    /**
     * CompanyController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param SerializerInterface $serializer
     * @param CompanyRepository $companyRepository
     * @param IndustryRepository $industryRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        Packages $assetsManager,
        SerializerInterface $serializer,
        CompanyRepository $companyRepository,
        IndustryRepository $industryRepository
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->serializer = $serializer;
        $this->companyRepository = $companyRepository;
        $this->industryRepository = $industryRepository;
    }

    /**
     * @Route("/companies", name="get_companies", methods={"GET"}, options = { "expose" = true })
     */
    public function getCompanies() {

        $companies = $this->companyRepository->findAll();

        $json = $this->serializer->serialize($companies, 'json', ['groups' => ['RESULTS_PAGE']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/industries", name="get_industries", methods={"GET"}, options = { "expose" = true })
     */
    public function getIndustries() {

        $industries = $this->industryRepository->findAll();

        $json = $this->serializer->serialize($industries, 'json', ['groups' => ['RESULTS_PAGE']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload
            ],
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/companies/{id}/upload-company-image", name="upload_company_image", methods={"POST"})
     * @param Company $company
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function uploadCompanyImage(Company $company, Request $request, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('company_image');

        if($uploadedFile) {

            $mimeType = $uploadedFile->getMimeType();
            $fileName = $this->uploaderHelper->uploadCompanyImage($uploadedFile);
            $companyImage = new CompanyPhoto();
            $companyImage->setFileName($fileName);
            $companyImage->setOriginalName($uploadedFile->getClientOriginalName() ?? $fileName);
            $companyImage->setMimeType($mimeType ?? 'application/octet-stream');
            $companyImage->setCompany($company);

            $entityManager->persist($companyImage);
            $entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'data' => [
                        'imagePath' => $this->assetsManager->getUrl($this->uploaderHelper->getPublicPath($companyImage->getPath()))
                    ]
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false
            ],
            Response::HTTP_BAD_REQUEST
        );

    }

    /**
     * @Route("/companies/{id}/add-video-url", name="add_video_url", methods={"POST"})
     * @param Company $company
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function addVideoURL(Company $company, Request $request, EntityManagerInterface $entityManager)
    {
        $videoURL = $request->request->get('video_url');

        $companyVideo = new CompanyVideo();
        $companyVideo->setUrl($videoURL);
        $companyVideo->setCompany($company);

        $entityManager->persist($companyVideo);
        $entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'data' => [
                    'videoURL' => $companyVideo->getUrl()
                ]
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/companies/{id}/upload-company-document", name="upload_company_document", methods={"POST"})
     * @param Company $company
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function uploadCompanyDocument(Company $company, Request $request, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('company_document');

        if($uploadedFile) {

            $mimeType = $uploadedFile->getMimeType();
            $fileName = $this->uploaderHelper->uploadCompanyDocument($uploadedFile);
            $companyDocument = new CompanyDocument();
            $companyDocument->setFileName($fileName);
            $companyDocument->setOriginalName($uploadedFile->getClientOriginalName() ?? $fileName);
            $companyDocument->setMimeType($mimeType ?? 'application/octet-stream');
            $companyDocument->setCompany($company);

            $entityManager->persist($companyDocument);
            $entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'data' => [
                        'imagePath' => $this->assetsManager->getUrl($this->uploaderHelper->getPublicPath($companyDocument->getPath()))
                    ]
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false
            ],
            Response::HTTP_BAD_REQUEST
        );

    }


}