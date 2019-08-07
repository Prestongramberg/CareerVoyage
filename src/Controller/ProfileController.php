<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\User;
use App\Form\AdminProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\SchoolAdministratorEditProfileFormType;
use App\Repository\RegionalCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
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
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * ProfileController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param UserRepository $userRepository
     * @param RegionalCoordinatorRepository $regionalCoordinatorRepository
     * @param CacheManager $cacheManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        UserRepository $userRepository,
        RegionalCoordinatorRepository $regionalCoordinatorRepository,
        CacheManager $cacheManager
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->userRepository = $userRepository;
        $this->regionalCoordinatorRepository = $regionalCoordinatorRepository;
        $this->cacheManager = $cacheManager;
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
        } elseif (($user->isSchoolAdministrator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(SchoolAdministratorEditProfileFormType::class, $user, $options);
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

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
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

        $this->denyAccessUnlessGranted('edit', $user);

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
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::PROFILE_PHOTO.'/'.$newFilename, 'squared_thumbnail_small')
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
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, User $user) {

        $user->setDeleted(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirectToRoute('welcome');
    }

    /**
     * @Route("/profile/{id}/deactivate", name="profile_deactivate")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deactivateAction(Request $request, User $user) {

        $user->setActivated(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
    }

    /**
     * @Route("/profile/{id}/reactivate", name="profile_reactivate")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reactivateAction(Request $request, User $user) {

        $user->setActivated(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
    }

}