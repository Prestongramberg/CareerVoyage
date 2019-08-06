<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\User;
use App\Form\AdminProfileFormType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Repository\RegionalCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ProfileController
 * @package App\Controller
 * @Route("/dashboard")
 */
class ProfileController extends AbstractController
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
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var RegionalCoordinatorRepository
     */
    private $regionalCoordinatorRepository;

    /**
     * ProfileController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param UserRepository $userRepository
     * @param RegionalCoordinatorRepository $regionalCoordinatorRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        UserRepository $userRepository,
        RegionalCoordinatorRepository $regionalCoordinatorRepository
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->userRepository = $userRepository;
        $this->regionalCoordinatorRepository = $regionalCoordinatorRepository;
    }

    /**
     * @Route("/profiles/{id}/view", name="profile_index", methods={"GET"})
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, User $user) {

        $user = $this->getUser();
        return $this->render('profile/index.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/profiles/{id}/edit", name="profile_edit")
     * @param Request $request
     * @param User $user
     * @return JsonResponse|Response
     */
    public function editAction(Request $request, User $user) {

        $this->denyAccessUnlessGranted('edit', $user);

        $options = [
            'method' => 'POST',
        ];

        if($user->isAdmin()) {
            $form = $this->createForm(AdminProfileFormType::class, $user, $options);
        } elseif (($user->isProfessional())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(ProfessionalEditProfileFormType::class, $user, $options);
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            if($user->getPlainPassword()) {
                $encodedPassword = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encodedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile successfully updated');
            return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);

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

        $deleteForm = $this->createForm(ProfessionalDeleteProfileFormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_delete', ['id' => $user->getId()])
        ]);

        $deactivateForm = $this->createForm(ProfessionalDeactivateProfileFormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_deactivate', ['id' => $user->getId()])
        ]);

        $reactivateForm = $this->createForm(ProfessionalReactivateProfileFormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_reactivate', ['id' => $user->getId()])
        ]);

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'deleteForm' => $deleteForm->createView(),
            'deactivateForm' => $deactivateForm->createView(),
            'reactivateForm' => $reactivateForm->createView(),
        ]);
    }

    /**
     * @Route("/profiles/{id}/photo/add", name="profile_photo_add", options = { "expose" = true })
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function profileAddPhotoAction(Request $request, User $user) {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $profilePhoto = $request->files->get('file');

        if($profilePhoto) {
            $newFilename = $this->uploaderHelper->upload($profilePhoto);
            $user->setPhoto($newFilename);
            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::PROFILE_PHOTO) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => 'uploads/'.UploaderHelper::PROFILE_PHOTO.'/'.$newFilename
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
     * @Route("/profile/{id}/delete", name="profile_delete")
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, ProfessionalUser $professionalUser) {

        $form = $this->createForm(ProfessionalDeleteProfileFormType::class, $professionalUser, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $professionalUser->setDeleted(true);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirectToRoute('welcome');
    }

    /**
     * @Route("/profile/{id}/deactivate", name="profile_deactivate")
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deactivateAction(Request $request, ProfessionalUser $professionalUser) {

        $form = $this->createForm(ProfessionalDeactivateProfileFormType::class, $professionalUser, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $professionalUser->setDeactivated(true);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('profile_edit', ['id' => $professionalUser->getId()]);
    }

    /**
     * @Route("/profile/{id}/reactivate", name="profile_reactivate")
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reactivateAction(Request $request, ProfessionalUser $professionalUser) {

        $form = $this->createForm(ProfessionalReactivateProfileFormType::class, $professionalUser, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $professionalUser->setDeactivated(false);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('profile_edit', ['id' => $professionalUser->getId()]);
    }

}