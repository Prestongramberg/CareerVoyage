<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
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
 * @Route("/dashboard")
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
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var CompanyPhotoRepository
     */
    private $companyPhotoRepository;

    /**
     * CompanyController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        Packages $assetsManager,
        CompanyRepository $companyRepository,
        CompanyPhotoRepository $companyPhotoRepository
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
    }

    /**
     * @Route("/companies", name="company_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $companies = $this->companyRepository->findBy([
            'approved' => false
        ]);

        $user = $this->getUser();
        return $this->render('company/index.html.twig', [
            'user' => $user,
            'companies' => $companies
        ]);
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

            $user->setCompany($company);

          /*  $logo = $form->get('logo')->getData();

            if($logo) {
                $newFilename = $this->uploaderHelper->uploadCompanyLogo($logo);
                $company->setLogo($newFilename);

                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::COMPANY_LOGO) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            $heroImage = $form->get('heroImage')->getData();

            if($heroImage) {
                $newFilename = $this->uploaderHelper->uploadHeroImage($heroImage);
                $company->setHeroImage($newFilename);
                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::HERO_IMAGE) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }*/

            $this->entityManager->persist($company);
            $this->entityManager->persist($user);
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
            'user' => $this->getUser(),
            'company' => $company
        ]);
    }

    /**
     * @Route("/companies/{id}/edit", name="company_edit")
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Company $company) {

        $this->denyAccessUnlessGranted('edit', $company);

        $user = $this->getUser();

        if(!$user instanceof ProfessionalUser) {
            throw new AccessDeniedException();
        }

        if(!$user->getCompany()) {
            return $this->redirectToRoute('company_new');
        }

        $form = $this->createForm(EditCompanyFormType::class, $company, [
            'method' => 'POST',
            'company' => $company
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Company $company */
            $company = $form->getData();

            $user->setCompany($company);

            /** @var UploadedFile $thumbnailImage */
            $thumbnailImage = $form->get('thumbnailImage')->getData();

            if($thumbnailImage) {
                $mimeType = $thumbnailImage->getMimeType();
                $newFilename = $this->uploaderHelper->upload($thumbnailImage, UploaderHelper::THUMBNAIL_IMAGE);
                $image = new Image();
                $image->setOriginalName($thumbnailImage->getClientOriginalName() ?? $newFilename);
                $image->setMimeType($mimeType ?? 'application/octet-stream');
                $image->setFileName($newFilename);
                $company->setThumbnailImage($image);
                $this->entityManager->persist($image);

                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::THUMBNAIL_IMAGE) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            /** @var UploadedFile $featuredImage */
            $featuredImage = $form->get('featuredImage')->getData();

            if($featuredImage) {
                $mimeType = $featuredImage->getMimeType();
                $newFilename = $this->uploaderHelper->upload($featuredImage, UploaderHelper::FEATURE_IMAGE);
                $image = new Image();
                $image->setOriginalName($featuredImage->getClientOriginalName() ?? $newFilename);
                $image->setMimeType($mimeType ?? 'application/octet-stream');
                $image->setFileName($newFilename);
                $company->setFeaturedImage($image);
                $this->entityManager->persist($image);
            }

            /** @var UploadedFile[] $photos */
            $photos = $form->get('photos')->getData();
            foreach($photos as $photo) {
                $mimeType = $photo->getMimeType();
                $newFilename = $this->uploaderHelper->upload($photo, UploaderHelper::COMPANY_PHOTO);
                $image = new CompanyPhoto();
                $image->setOriginalName($photo->getClientOriginalName() ?? $newFilename);
                $image->setMimeType($mimeType ?? 'application/octet-stream');
                $image->setFileName($newFilename);
                $company->addCompanyPhoto($image);
                $this->entityManager->persist($image);
            }

            /** @var UploadedFile[] $photos */
            $resources = $form->get('resources')->getData();
            foreach($resources as $resource) {
                $mimeType = $resource->getMimeType();
                $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::COMPANY_RESOURCE);
                $companyResource = new CompanyResource();
                $companyResource->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
                $companyResource->setMimeType($mimeType ?? 'application/octet-stream');
                $companyResource->setFileName($newFilename);
                $company->addCompanyResource($companyResource);
                $this->entityManager->persist($companyResource);
            }

            $this->entityManager->persist($company);
            $this->entityManager->flush();
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/companies/{company_id}/photos/{image_id}/remove", name="company_photo_remove")
     * @ParamConverter("image", options={"id" = "image_id"})
     * @ParamConverter("company", options={"id" = "company_id"})
     * @param Company $company
     * @param Request $request
     * @param CompanyPhoto $image
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeCompanyPhotoAction(Company $company, Request $request, CompanyPhoto $image) {

        $this->denyAccessUnlessGranted('delete', $image);

        $this->entityManager->remove($image);
        $this->entityManager->flush();
        return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);
    }
}