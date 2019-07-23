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
use App\Mailer\RequestsMailer;
use App\Repository\AdminUserRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProfessionalUserRepository;
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
     * @var AdminUserRepository
     */
    private $adminUserRepository;

    /**
     * @var RequestsMailer
     */
    private $requestsMailer;

    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

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
     * @param AdminUserRepository $adminUserRepository
     * @param RequestsMailer $requestsMailer
     * @param ProfessionalUserRepository $professionalUserRepository
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
        ProfessionalUserRepository $professionalUserRepository
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
        $this->professionalUserRepository = $professionalUserRepository;
    }

    /**
     * @Route("/companies", name="company_index", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $user = $this->getUser();
        return $this->render('company/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("ROLE_PROFESSIONAL_USER")
     *
     * @Route("/companies/new", name="company_new", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request) {

        $user = $this->getUser();

        if(!$user instanceof ProfessionalUser) {
            throw new AccessDeniedException();
        }

        if($user->getCompany() && $user->getCompany()->getOwner() && $user->getCompany()->getOwner()->getId() === $user->getId()) {
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
            $company->setOwner($user);

            $user->setCompany($company);

            $adminUsers = $this->adminUserRepository->findAll();
            $adminUser = $adminUsers[0];

            // create a new company request
            $newCompanyRequest = new NewCompanyRequest();
            $newCompanyRequest->setCreatedBy($user);
            $newCompanyRequest->setCompany($company);
            $newCompanyRequest->setNeedsApprovalBy($adminUser);

            $this->entityManager->persist($newCompanyRequest);
            $this->entityManager->persist($company);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->requestsMailer->newCompanyNeedsApproval($newCompanyRequest);

            return $this->redirectToRoute('company_view', ['id' => $company->getId()]);

        }

        return $this->render('company/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/companies/{id}/view", name="company_view", options = { "expose" = true })
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
     * @Route("/companies/{id}/edit", name="company_edit", options = { "expose" = true })
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

            $companyResources = $company->getCompanyResources();
            foreach($companyResources as $companyResource) {

                if(!$companyResource->getFile()) {
                    continue;
                }

                /** @var UploadedFile $file */
                $file = $companyResource->getFile();
                $mimeType = $file->getMimeType();
                $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::COMPANY_RESOURCE);
                $companyResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
                $companyResource->setMimeType($mimeType ?? 'application/octet-stream');
                $companyResource->setFileName($newFilename);
                $companyResource->setFile(null);
                $this->entityManager->persist($companyResource);
            }

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/companies/{companyID}/users/{userID}/remove", name="company_remove_user", options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "companyID"})
     * @ParamConverter("user", options={"id" = "userID"})
     *
     * @param Request $request
     * @param Company $company
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function removeUserFromCompanyAction(Request $request, Company $company, ProfessionalUser $professionalUser) {

        /** @var User $user */
        $user = $this->getUser();

        $canRemove = ($user->isAdmin() ||
                $company->getOwner()->getId() === $user->getId() ||
                $user->getId() === $professionalUser->getId())
            && $professionalUser->getCompany()->getId() === $company->getId();

        if($canRemove) {
            $professionalUser->setCompany(null);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
            $this->addFlash('success', 'user removed from company');
        } else {
            $this->addFlash('error', 'user cannot be removed from company');
        }

        return $this->redirectToRoute('company_index');
    }

    /**
     * @Route("/companies/{id}/delete", name="company_delete", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function deleteCompanyAction(Company $company, Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $canDelete = $user->isAdmin() || $company->getOwner()->getId() === $user->getId();

        if($canDelete) {
            $this->entityManager->remove($company);
            $this->entityManager->flush();

            $this->addFlash('success', 'company deleted');
        } else {
            $this->addFlash('error', 'company can not be deleted');
        }

        return $this->redirectToRoute('company_index');
    }

}