<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\CompanyVideo;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\Experience;
use App\Entity\ExperienceFile;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\Registration;
use App\Entity\RequestPossibleApprovers;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\CompanyInviteFormType;
use App\Form\EditCompanyExperienceType;
use App\Form\EditCompanyFormType;
use App\Form\EducatorRegisterStudentsForExperienceFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewCompanyExperienceType;
use App\Form\ProfessionalEditProfileFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Mailer\ExperienceMailer;
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
use App\Util\ServiceHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Proxies\__CG__\App\Entity\Video;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Psr\Log\LoggerInterface;

/**
 * Class ProfileController
 * @package App\Controller
 * @Route("/dashboard")
 */
class CompanyController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

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

        // if user already has company created then don't let them create another
        if($user->getCompany() && $user->getCompany()->getOwner() && $user->getCompany()->getOwner()->getId() === $user->getId()) {
            return $this->redirectToRoute('company_view', ['id' => $user->getCompany()->getId()]);
        }

        $company = new Company();

        $options = [
            'method' => 'POST',
            'company' => $company,
            'skip_validation' => $request->request->get('skip_validation', false),
        ];

        $form = $this->createForm(NewCompanyFormType::class, $company, $options);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Company $company */
            $company = $form->getData();
            $shouldAttemptGeocode = $company->getStreet() && $company->getCity() && $company->getState() && $company->getZipcode();
            if($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($company->getFormattedAddress())) {
                $company->setLongitude($coordinates['lng']);
                $company->setLatitude($coordinates['lat']);
            }
            $company->setOwner($user);
            $user->setCompany($company);
            $adminUsers = $this->adminUserRepository->findAll();
            $adminUser = $adminUsers[0];

            // create a new company request
            $newCompanyRequest = new NewCompanyRequest();
            $newCompanyRequest->setCreatedBy($user);
            $newCompanyRequest->setCompany($company);
            $newCompanyRequest->setNeedsApprovalBy($adminUser);

            $adminUsers = $this->userRepository->findByRole(User::ROLE_ADMIN_USER);

            foreach($adminUsers as $adminUser) {
                $possibleApprover = new RequestPossibleApprovers();
                $possibleApprover->setPossibleApprover($adminUser);
                $possibleApprover->setRequest($newCompanyRequest);
                $this->entityManager->persist($possibleApprover);
            }

            $this->entityManager->persist($newCompanyRequest);
            $this->entityManager->persist($company);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->requestsMailer->newCompanyRequest($newCompanyRequest);
            $this->requestsMailer->companyAwaitingApproval($newCompanyRequest);

            $this->addFlash('success', 'Company successfully created. While you\'re company is waiting for approval go ahead and add some images and videos!');

            return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);

        }

        if($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView('api/form/secondary_industry_form_new_company_field.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return $this->render('company/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/companies/{id}/view", name="company_view", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Company $company) {

        $user = $this->getUser();

        return $this->render('company/view.html.twig', [
            'user' => $user,
            'company' => $company,
        ]);
    }

    /**
     * @IsGranted("ROLE_PROFESSIONAL_USER")
     * @Route("/companies/{id}/join", name="company_join", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
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

        $this->requestsMailer->joinCompanyRequest($joinCompanyRequest);

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
            'user' => $user,
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
           'company' => $company->getId(),
        ]);

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('company/professionals.html.twig', [
            'user' => $user,
            'company' => $company,
            'professionals' => $professionals,
        ]);
    }

    /**
     * @Route("/companies/professionals/{id}/remove", name="company_remove_user", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param ProfessionalUser $professional
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyProfessionalRemoveAction(Request $request, ProfessionalUser $professional) {

        $company = $professional->getCompany();
        /** @var User $user */
        $user = $this->getUser();
        $canRemove = false;

        if($user->isAdmin()) {
            $canRemove = true;
        } else if($company->isUserOwner($professional)) {
            // the owner of the company can't be removed unless someone else becomes the owner first
            $canRemove = false;
        } else if ($user->getId() === $professional->getId()) {
            $canRemove = true;
        }

        if(!$canRemove) {
            $this->addFlash('error', 'That user cannot be removed from the company.');
            return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
        }

        $companyId = $professional->getCompany()->getId();
        $professional->setCompany(null);
        $this->entityManager->persist($professional);
        $this->entityManager->flush();

        $this->addFlash('success', 'Professional removed from company');

        return $this->redirectToRoute('company_view', ['id' => $companyId]);
    }

    /**
     * @Route("/companies/{id}/thumbnail/add", name="company_thumbnail_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return JsonResponse
     */
    public function companyAddThumbnailAction(Request $request, Company $company) {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $thumbnailImage = $request->files->get('file');

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

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::THUMBNAIL_IMAGE.'/'.$newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/{id}/featured/add", name="company_featured_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return JsonResponse
     */
    public function companyAddFeaturedAction(Request $request, Company $company) {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $featuredImage = $request->files->get('file');

        if($featuredImage) {
            $mimeType = $featuredImage->getMimeType();
            $newFilename = $this->uploaderHelper->upload($featuredImage, UploaderHelper::FEATURE_IMAGE);
            $image = new Image();
            $image->setOriginalName($featuredImage->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $company->setFeaturedImage($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::FEATURE_IMAGE) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::FEATURE_IMAGE.'/'.$newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/{id}/resource/add", name="company_resource_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function companyAddResourceAction(Request $request, Company $company) {

        $this->denyAccessUnlessGranted('edit', $company);

        /** @var UploadedFile $file */
        $file = $request->files->get('resource');
        $title = $request->request->get('title');
        $linkToWebsite = $request->request->get('linkToWebsite');
        $description = $request->request->get('description');

        if(!$file && !$linkToWebsite) {
            return new JsonResponse(
                [
                    'success' => false,

                ], Response::HTTP_BAD_REQUEST
            );
        }

        if(!$title) {
            return new JsonResponse(
                [
                    'success' => false,

                ], Response::HTTP_BAD_REQUEST
            );
        }

        $companyResource = new CompanyResource();

        if($file) {
            $mimeType = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::COMPANY_RESOURCE);
            $companyResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $companyResource->setMimeType($mimeType ?? 'application/octet-stream');
            $companyResource->setFileName($newFilename);
            $companyResource->setFile(null);
        }

        if($linkToWebsite) {
            $companyResource->setLinkToWebsite($linkToWebsite);
        }

        $companyResource->setCompany($company);
        $companyResource->setDescription($description ? $description : null);
        $companyResource->setTitle($title);
        $this->entityManager->persist($companyResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'url' => $file ? $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::COMPANY_RESOURCE.'/'.$newFilename : '',
                'id' => $companyResource->getId(),
                'title' => $title,
                'description' => $description,

            ], Response::HTTP_OK
        );

    }

    /**
     * @Route("/companies/resources/{id}/edit", name="company_resource_edit", options = { "expose" = true })
     * @param Request $request
     * @param CompanyResource $companyResource
     * @return JsonResponse
     */
    public function companyEditResourceAction(Request $request, CompanyResource $companyResource) {

        $this->denyAccessUnlessGranted('edit', $companyResource->getCompany());

        /** @var UploadedFile $file */
        $file = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($file && $title && $description) {
            $mimeType = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::COMPANY_RESOURCE);
            $companyResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $companyResource->setMimeType($mimeType ?? 'application/octet-stream');
            $companyResource->setFileName($newFilename);
            $companyResource->setFile(null);
            $companyResource->setDescription($description);
            $companyResource->setTitle($title);
            $this->entityManager->persist($companyResource);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => 'uploads/'.UploaderHelper::COMPANY_RESOURCE.'/'.$newFilename,
                    'id' => $companyResource->getId(),
                    'title' => $title,
                    'description' => $description,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/videos/{id}/edit", name="company_video_edit", options = { "expose" = true })
     * @param Request $request
     * @param CompanyVideo $video
     * @return JsonResponse
     */
    public function companyEditVideoAction(Request $request, CompanyVideo $video) {

        $this->denyAccessUnlessGranted('edit', $video->getCompany());

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');

        if($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{id}/video/add", name="company_video_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return JsonResponse
     */
    public function companyAddVideoAction(Request $request, Company $company) {

        $this->denyAccessUnlessGranted('edit', $company);

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');

        if($name && $videoId) {
            $video = new CompanyVideo();
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setCompany($company);
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/videos/{id}/remove", name="company_video_remove", options = { "expose" = true })
     * @param Request $request
     * @param CompanyVideo $companyVideo
     * @return JsonResponse
     */
    public function companyRemoveVideoAction(Request $request, CompanyVideo $companyVideo) {

        $this->denyAccessUnlessGranted('edit', $companyVideo->getCompany());

        $this->entityManager->remove($companyVideo);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }


    /**
     * @Route("/companies/resource/{id}/remove", name="company_resource_remove", options = { "expose" = true })
     * @param Request $request
     * @param CompanyResource $companyResource
     * @return JsonResponse
     */
    public function companyRemoveResourceAction(Request $request, CompanyResource $companyResource) {

        $this->denyAccessUnlessGranted('edit', $companyResource->getCompany());

        $this->entityManager->remove($companyResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{id}/photos/add", name="company_photos_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return JsonResponse
     */
    public function companyAddPhotosAction(Request $request, Company $company) {

        $this->denyAccessUnlessGranted('edit', $company);

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $photo = $request->files->get('file');

        if($photo) {
            $mimeType = $photo->getMimeType();
            $newFilename = $this->uploaderHelper->upload($photo, UploaderHelper::COMPANY_PHOTO);
            $image = new CompanyPhoto();
            $image->setOriginalName($photo->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $company->addCompanyPhoto($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::COMPANY_PHOTO) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::COMPANY_PHOTO.'/'.$newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/photos/{id}/remove", name="company_photo_remove", options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "company_id"})
     * @ParamConverter("companyResource", options={"id" = "photo_id"})
     * @param Request $request
     * @param CompanyPhoto $companyPhoto
     * @return JsonResponse
     */
    public function companyRemovePhotoAction(Request $request, CompanyPhoto $companyPhoto) {

        $this->denyAccessUnlessGranted('edit', $companyPhoto->getCompany());

        $this->entityManager->remove($companyPhoto);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
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

        $options = [
            'method' => 'POST',
            'company' => $company,
            'skip_validation' => $request->request->get('skip_validation', false),
        ];

        $form = $this->createForm(EditCompanyFormType::class, $company, $options);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Company $company */
            $company = $form->getData();
            $shouldAttemptGeocode = $company->getStreet() && $company->getCity() && $company->getState() && $company->getZipcode();
            if($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($company->getFormattedAddress())) {
                $company->setLongitude($coordinates['lng']);
                $company->setLatitude($coordinates['lat']);
            }
            $this->entityManager->persist($company);
            $this->entityManager->flush();

            $this->addFlash('success', 'Company successfully updated');

            return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);
        }
        if($form->isSubmitted() && !$form->isValid()) {
          $this->addFlash('error', 'Company was not updated. Please check all tabs for required information.');
        }

        if($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView('api/form/secondary_industry_form_field.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/companies/{id}/delete", name="company_delete", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function deleteCompanyAction(Company $company, Request $request) {

        $this->denyAccessUnlessGranted('edit', $company);

        /** @var User $user */
        $user = $this->getUser();

        $this->entityManager->remove($company);
        $this->entityManager->flush();

        $this->addFlash('success', 'Company deleted');

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
        $experience = new CompanyExperience();

        $form = $this->createForm(NewCompanyExperienceType::class, $experience, [
            'method' => 'POST',
            'company' => $company,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var CompanyExperience $experience */
            $experience = $form->getData();

            $shouldAttemptGeocode = $experience->getStreet() && $experience->getCity() && $experience->getState() && $experience->getZipcode();
            if($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($experience->getFormattedAddress())) {
                $experience->setLongitude($coordinates['lng']);
                $experience->setLatitude($coordinates['lat']);
            }

            $this->entityManager->persist($experience);

            $experience->setCompany($company);

            $registration = new Registration();
            $registration->setUser($this->getUser());
            $registration->setExperience($experience);

            $this->entityManager->persist($registration);

            $this->entityManager->flush();

            $this->addFlash('success', 'Experience successfully created!');

            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }

        return $this->render('company/new_experience.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/companies/experiences/{id}/edit", name="company_experience_edit", options = { "expose" = true })
     * @param Request $request
     * @param CompanyExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editExperienceAction(Request $request, CompanyExperience $experience) {

        $this->denyAccessUnlessGranted('edit', $experience->getCompany());

        $company = $experience->getCompany();

        $user = $this->getUser();

        $form = $this->createForm(EditCompanyExperienceType::class, $experience, [
            'method' => 'POST',
            'company' => $company,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var CompanyExperience $experience */
            $experience = $form->getData();

            $shouldAttemptGeocode = $experience->getStreet() && $experience->getCity() && $experience->getState() && $experience->getZipcode();
            if($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($experience->getFormattedAddress())) {
                $experience->setLongitude($coordinates['lng']);
                $experience->setLatitude($coordinates['lat']);
            }

            $this->entityManager->persist($experience);
            $experience->setCompany($company);
            $this->entityManager->flush();
            $this->addFlash('success', 'Experience successfully updated!');

            return $this->redirectToRoute('company_experience_edit', ['id' => $experience->getId()]);
        }

        return $this->render('company/edit_experience.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'user' => $user,
            'experience' => $experience,
        ]);
    }


    /**
     * @Route("/companies/experiences/{id}/view", name="company_experience_view", options = { "expose" = true })
     * @param Request $request
     * @param CompanyExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewExperienceAction(Request $request, CompanyExperience $experience) {

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('company/view_experience.html.twig', [
            'user' => $user,
            'experience' => $experience
        ]);
    }

    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/companies/experiences/{id}/students/register", name="company_experience_student_register", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param CompanyExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyExperienceStudentRegisterAction(Request $request, CompanyExperience $experience) {
        $studentIdToRegister = $request->request->get('studentId');
        $studentToRegister = $this->studentUserRepository->find($studentIdToRegister);

        if($experience->getAvailableSpaces() === 0) {
            $this->addFlash('error', sprintf('Could not register students. 0 spots left.'));
            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }
        /** @var User $user */
        $user = $this->getUser();
        $registerRequest = new EducatorRegisterStudentForCompanyExperienceRequest();
        $registerRequest->setCreatedBy($user);
        $registerRequest->setNeedsApprovalBy($experience->getEmployeeContact());
        $registerRequest->setCompanyExperience($experience);
        $registerRequest->setStudentUser($studentToRegister);
        $this->entityManager->persist($registerRequest);
        $this->entityManager->flush();
        $this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequest($registerRequest);
        $this->addFlash('success', 'Registration request successfully sent.');
        return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/companies/experiences/{id}/students/deregister", name="company_experience_student_deregister", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param CompanyExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function companyExperienceStudentDeregisterAction(Request $request, CompanyExperience $experience) {
        $studentIdToDeregister = $request->request->get('studentId');
        $studentToDeregister = $this->studentUserRepository->find($studentIdToDeregister);

        $deregisterStudentForExperience = $this->educatorRegisterStudentForExperienceRequestRepository->getByStudentAndExperience($studentToDeregister, $experience);

        $deregisterRequest = $this->requestRepository->find($deregisterStudentForExperience);

        if ($deregisterRequest->getApproved()) {
            $experience->setAvailableSpaces($experience->getAvailableSpaces() + 1);
        }

        $registration = $this->registrationRepository->getByUserAndExperience($studentToDeregister, $experience);

        /** @var ProfessionalUser $companyOwner */
        $companyOwner = $experience->getCompany()->getOwner();

        if($companyOwner->getEmail()) {
            $this->requestsMailer->userDeregisterFromEvent($studentToDeregister, $companyOwner, $experience);
        }

        $educators = $studentToDeregister->getEducatorUsers();

        foreach($educators as $educator) {
            if($educator->getEmail()) {
                $this->requestsMailer->userDeregisterFromEvent($studentToDeregister, $educator, $experience);
            }
        }

        if($studentToDeregister->getEmail()) {
            $this->requestsMailer->userDeregisterFromEvent($studentToDeregister, $studentToDeregister, $experience);
        }

        $this->entityManager->remove($deregisterStudentForExperience);
        $this->entityManager->remove($deregisterRequest);
        if ($registration) {
            $this->entityManager->remove($registration);
        }
        $this->entityManager->persist($experience);
        $this->entityManager->flush();
        $this->addFlash('success', 'Student has been removed from this experience.');
        return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @Route("/companies/experiences/{id}/remove", name="company_experience_remove", options = { "expose" = true })
     * @param Request $request
     * @param CompanyExperience $experience
     * @param LoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceRemoveAction(Request $request, CompanyExperience $experience) {

        $company = $experience->getCompany();
        $this->denyAccessUnlessGranted('edit', $experience->getCompany());

        $message = $request->query->get('cancellationMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {
            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $message);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully removed!');

        return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
    }

    /**
     * @Route("/companies/experiences/{id}/file/add", name="company_experience_file_add", options = { "expose" = true })
     * @param Request $request
     * @param CompanyExperience $experience
     * @return JsonResponse
     */
    public function experienceAddFileAction(Request $request, CompanyExperience $experience) {

        $this->denyAccessUnlessGranted('edit', $experience->getCompany());

        /** @var UploadedFile $resource */
        $resource = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($resource && $title) {
            $mimeType = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::EXPERIENCE_FILE);
            $file = new ExperienceFile();
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
            $file->setExperience($experience);
            $file->setDescription($description ? $description : null);
            $file->setTitle($title);
            $this->entityManager->persist($file);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::EXPERIENCE_FILE.'/'.$newFilename,
                    'id' => $file->getId(),
                    'title' => $title,
                    'description' => $description,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/experiences/file/{id}/edit", name="company_experience_file_edit", options = { "expose" = true })
     * @param Request $request
     * @param ExperienceFile $file
     * @return JsonResponse
     */
    public function experienceEditFileAction(Request $request, ExperienceFile $file) {

        $this->denyAccessUnlessGranted('edit', $file->getExperience()->getCompany());

        /** @var UploadedFile $resource */
        $resource = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($title) {
            $file->setTitle($title);
        }

        if($description) {
            $file->setDescription($description);
        }

        if($resource) {
            $mimeType = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::EXPERIENCE_FILE);
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();


        return new JsonResponse(
            [
                'success' => true,
                'url' => $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::EXPERIENCE_FILE.'/'. $file->getFileName(),
                'id' => $file->getId(),
                'title' => $file->getTitle(),
                'description' => $file->getDescription(),

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/experiences/files/{id}/remove", name="company_experience_file_remove", options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "company_id"})
     * @ParamConverter("companyResource", options={"id" = "resource_id"})
     * @param Request $request
     * @param ExperienceFile $experienceFile
     * @return JsonResponse
     */
    public function experienceRemoveFileAction(Request $request, ExperienceFile $experienceFile) {

        $this->denyAccessUnlessGranted('edit', $experienceFile->getExperience()->getCompany());

        $this->entityManager->remove($experienceFile);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/companies/experiences/{id}/students/forward", name="company_experience_bulk_notify", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param CompanyExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function companyExperienceBulkNotifyAction(Request $request, CompanyExperience $experience) {
        $message = $request->get('message', '');

        $message = sprintf("Event: %s Message: %s", $experience->getTitle(), $message);

        $students = $request->get('students');

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        foreach ($students as $student) {

            /** @var StudentUser $student */
            $student = $this->studentUserRepository->find($student);
            $this->experienceMailer->experienceForwardToStudent($experience, $student, $message, $loggedInUser);


            $chat = $this->chatRepository->findOneBy([
                'userOne' => $loggedInUser,
                'userTwo' => $student
            ]);

            if(!$chat) {
                $chat = $this->chatRepository->findOneBy([
                    'userOne' => $student,
                    'userTwo' => $loggedInUser
                ]);
            }

            // if a chat doesn't exist then let's create one!
            if(!$chat) {
                $chat = new Chat();
                $chat->setUserOne($student);
                $chat->setUserTwo($loggedInUser);
                $this->entityManager->persist($chat);
                $this->entityManager->flush();
            }


            $notice = $message;

            $chatMessage = new ChatMessage();
            $chatMessage->setBody($notice);
            $chatMessage->setSentFrom($loggedInUser);
            $chatMessage->setSentAt(new \DateTime());
            $chatMessage->setChat($chat);

            // Figure out which user to message from the chat object
            $userToMessage = $chat->getUserOne()->getId() === $loggedInUser->getId() ? $chat->getUserTwo() : $chat->getUserOne();
            $chatMessage->setSentTo($userToMessage);

            $this->entityManager->persist($chatMessage);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Experience has been sent to students!');

        return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
    }
}
