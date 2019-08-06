<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Experience;
use App\Entity\ExperienceFile;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\CompanyInviteFormType;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewExperienceType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\AdminUserRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\UserRepository;
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
     * @param SecurityMailer $securityMailer
     * @param ProfessionalUserRepository $professionalUserRepository
     * @param JoinCompanyRequestRepository $joinCompanyRequestRepository
     * @param UserRepository $userRepository
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
        UserRepository $userRepository
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

        $options = [
            'method' => 'POST',
            'company' => $company,
            'skip_validation' => $request->request->get('skip_validation', false)
        ];

        $form = $this->createForm(NewCompanyFormType::class, $company, $options);

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


            $this->entityManager->persist($newCompanyRequest);
            $this->entityManager->persist($company);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->requestsMailer->newCompanyRequest($newCompanyRequest);

            $this->addFlash('success', 'Company successfully created');

            return $this->redirectToRoute('company_view', ['id' => $company->getId()]);

        }

        if($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView('api/form/secondary_industry_form_field.html.twig', [
                        'form' => $form->createView()
                    ])
                ], Response::HTTP_BAD_REQUEST
            );
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
     * @Route("/companies/{id}/join", name="company_join", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function joinAction(Request $request, Company $company) {

        /** @var User $user */
        $user = $this->getUser();

        $requests = $this->joinCompanyRequestRepository->getJoinCompanyRequestsByCompanyAndUser($company, $user);

        if(count($requests) > 0) {
            $this->addFlash('error', 'You have already made a request to join that company.');
            return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
        }

        $joinCompanyRequest = new JoinCompanyRequest();
        $joinCompanyRequest->setCompany($company);
        $joinCompanyRequest->setCreatedBy($user);
        $joinCompanyRequest->setNeedsApprovalBy($company->getOwner());
        $joinCompanyRequest->setType(JoinCompanyRequest::TYPE_USER_TO_COMPANY);
        $this->entityManager->persist($joinCompanyRequest);
        $this->entityManager->flush();

        $this->requestsMailer->userToCompanyRequest($joinCompanyRequest);

        $this->addFlash('success', 'Request successfully sent!');
        return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
    }

    /**
     * @Route("/companies/{id}/invite", name="company_invite", options = { "expose" = true }, methods={"GET", "POST"})
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function inviteAction(Request $request, Company $company) {

        $this->denyAccessUnlessGranted('edit', $company);

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(CompanyInviteFormType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $emails = $form->get('emails')->getData();
            $emails = explode(',', $emails);
            foreach($emails as $email) {

                // for now we are just skipping users that already exist in the system
                $existingUser = $this->userRepository->getByEmailAddress($email);
                if($existingUser) {
                    continue;
                } else {
                    $professionalUser = new ProfessionalUser();
                    $professionalUser->setEmail($email);
                    $professionalUser->initializeNewUser();
                    $professionalUser->setPasswordResetToken();
                    $this->entityManager->persist($professionalUser);
                }
                $joinCompanyRequest = new JoinCompanyRequest();
                $joinCompanyRequest->setCompany($company);
                $joinCompanyRequest->setCreatedBy($this->getUser());
                $joinCompanyRequest->setIsFromCompany(true);
                $joinCompanyRequest->setNeedsApprovalBy($professionalUser);
            }

            $this->entityManager->flush();
            $this->securityMailer->sendAccountActivation($professionalUser);
            $this->requestsMailer->joinCompanyRequest($joinCompanyRequest);

            $this->addFlash('success', 'Company invites successfully sent. ');
            return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);
        }

        return $this->render('company/invite.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/companies/{id}/professionals", name="company_professionals", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyProfessionalsAction(Request $request, Company $company) {

        $professionals = $this->professionalUserRepository->findBy([
           'company' => $company->getId()
        ]);

        /** @var User $user */
        $user = $this->getUser();
        $isOwner = false;
        if($user->isProfessional() && $user->getCompany() && $user->getCompany()->getId() === $company->getId()) {
            $isOwner = true;
        }

        return $this->render('company/professionals.html.twig', [
            'user' => $user,
            'company' => $company,
            'professionals' => $professionals,
            'isOwner' => $isOwner
        ]);
    }

    /**
     * @Route("/companies/{companyID}/professionals/{professionalID}/remove", name="company_professional_remove", options = { "expose" = true }, methods={"POST"})
     * @ParamConverter("company", options={"id" = "companyID"})
     * @ParamConverter("professional", options={"id" = "professionalID"})
     * @param Request $request
     * @param Company $company
     * @param ProfessionalUser $professional
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyProfessionalRemoveAction(Request $request, Company $company, ProfessionalUser $professional) {


        if($professional->isOwner($company)) {
            $this->addFlash('error', 'you do not have permission to do this');

            return $this->redirectToRoute('company_professionals', ['id' => $company->getId()]);
        }

        if($professional->getCompany()->getId() === $company->getId()) {
            $professional->setCompany(null);
            $this->entityManager->persist($professional);
            $this->entityManager->flush();

        }

        $this->addFlash('success', 'professional removed from company');

        return $this->redirectToRoute('company_professionals', ['id' => $company->getId()]);
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

        $options = [
            'method' => 'POST',
            'company' => $company,
            'skip_validation' => $request->request->get('skip_validation', false)
        ];

        $form = $this->createForm(EditCompanyFormType::class, $company, $options);

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

            // resource
            /** @var CompanyResource $resource */
            $resource = $form->get('resources')->getData();
            if($resource->getFile() && $resource->getDescription() && $resource->getTitle()) {
                $file = $resource->getFile();
                $mimeType = $file->getMimeType();
                $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::COMPANY_RESOURCE);
                $resource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
                $resource->setMimeType($mimeType ?? 'application/octet-stream');
                $resource->setFileName($newFilename);
                $resource->setFile(null);
                $resource->setCompany($company);
                $this->entityManager->persist($resource);
            }

            // video
            $video = $form->get('videos')->getData();
            if($video->getName() && $video->getVideoId()) {
                $video->setCompany($company);
                $this->entityManager->persist($video);
            }

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            $this->addFlash('success', 'Company successfully updated');

            return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);
        }

        if($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView('api/form/secondary_industry_form_field.html.twig', [
                        'form' => $form->createView()
                    ])
                ], Response::HTTP_BAD_REQUEST
            );
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

            // if the user we are removing is the owner of the company
            if($company->getOwner()->getId() === $professionalUser->getId()) {
                $company->setOwner(null);
            }


            $professionalUser->setCompany(null);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->persist($company);
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

    /**
     * @Route("/companies/{id}/experiences/create", name="company_experience_create", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createExperienceAction(Request $request, Company $company) {

        $this->denyAccessUnlessGranted('edit', $company);

        $user = $this->getUser();

        $experience = new Experience();
        $form = $this->createForm(NewExperienceType::class, $experience, [
            'method' => 'POST',
            'company' => $company
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Experience $experience */
            $experience = $form->getData();

            $this->entityManager->persist($experience);

            /** @var ExperienceFile $resource */
            $resource = $form->get('resources')->getData();
            if($resource->getFile() && $resource->getDescription() && $resource->getTitle()) {
                $file = $resource->getFile();
                $mimeType = $file->getMimeType();
                $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::EXPERIENCE_FILE);
                $resource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
                $resource->setMimeType($mimeType ?? 'application/octet-stream');
                $resource->setFileName($newFilename);
                $resource->setFile(null);
                $resource->setExperience($experience);
                $this->entityManager->persist($resource);
            }

            $experience->setCompany($company);

            $this->entityManager->flush();

            $this->addFlash('success', 'Experience successfully created!');

            return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
        }

        return $this->render('company/new_experience.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/experiences/{id}/edit", name="company_experience_edit", options = { "expose" = true })
     * @param Request $request
     * @param Experience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editExperienceAction(Request $request, Experience $experience) {

        $this->denyAccessUnlessGranted('edit', $experience);

        $company = $experience->getCompany();

        $user = $this->getUser();

        $form = $this->createForm(NewExperienceType::class, $experience, [
            'method' => 'POST',
            'company' => $company
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Experience $experience */
            $experience = $form->getData();

            $this->entityManager->persist($experience);

            /** @var ExperienceFile $resource */
            $resource = $form->get('resources')->getData();
            if($resource->getFile() && $resource->getDescription() && $resource->getTitle()) {
                $file = $resource->getFile();
                $mimeType = $file->getMimeType();
                $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::EXPERIENCE_FILE);
                $resource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
                $resource->setMimeType($mimeType ?? 'application/octet-stream');
                $resource->setFileName($newFilename);
                $resource->setFile(null);
                $resource->setExperience($experience);
                $this->entityManager->persist($resource);
            }

            $experience->setCompany($company);

            $this->entityManager->flush();

            $this->addFlash('success', 'Experience successfully updated!');

            return $this->redirectToRoute('company_experience_edit', ['id' => $experience->getId()]);
        }

        return $this->render('company/edit_experience.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'user' => $user,
            'experience' => $experience
        ]);
    }

}