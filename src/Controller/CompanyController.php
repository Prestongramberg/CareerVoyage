<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyImage;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
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
 * Class ProfileController
 * @package App\Controller
 * @Route("/admin")
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
     * CompanyController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        Packages $assetsManager
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
    }


    /**
     * @Route("/companies", name="company_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $user = $this->getUser();
        return $this->render('company/index.html.twig', [
            'user' => $user
        ]);
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
            $companyImage = new CompanyImage();
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
     * @IsGranted("ROLE_PROFESSIONAL_USER")
     *
     * @Route("/companies/new", name="company_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request) {

        $user = $this->getUser();

        if(!$user instanceof ProfessionalUser) {
            throw new AccessDeniedException();
        }

        if($user->getCompany()) {
            return $this->redirectToRoute('company_view', ['id' => $user->getCompany()->getId()]);
        }

        $company = new Company();
        $form = $this->createForm(NewCompanyFormType::class, $company, [
            'method' => 'POST',
            'company' => $company
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Company $company */
            $company = $form->getData();

            $logo = $form->get('logo')->getData();

            if($logo) {
                $newFilename = $this->uploaderHelper->uploadCompanyLogo($logo);
                $company->setLogo($newFilename);
            }

            $this->entityManager->persist($company);
            $this->entityManager->flush();
        }

        return $this->render('company/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/companies/{id}/view", name="company_view")
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Company $company) {

        return $this->render('company/view.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/companies/{id}/edit", name="company_edit")
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Company $company) {

        $professionalUser = $this->getUser();

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'user' => $professionalUser
        ]);
    }
}