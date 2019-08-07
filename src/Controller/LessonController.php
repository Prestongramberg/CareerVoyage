<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonResource;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EditLessonType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
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

/**
 * Class LessonController
 * @package App\Controller
 * @Route("/dashboard")
 */
class LessonController extends AbstractController
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
     * @var LessonFavoriteRepository
     */
    private $lessonFavoriteRepository;

    /**
     * @var LessonTeachableRepository
     */
    private $lessonTeachableRepository;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * LessonController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param LessonFavoriteRepository $lessonFavoriteRepository
     * @param LessonTeachableRepository $lessonTeachableRepository
     * @param CacheManager $cacheManager
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
        LessonFavoriteRepository $lessonFavoriteRepository,
        LessonTeachableRepository $lessonTeachableRepository,
        CacheManager $cacheManager
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
        $this->lessonFavoriteRepository = $lessonFavoriteRepository;
        $this->lessonTeachableRepository = $lessonTeachableRepository;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @Route("/lessons", name="lesson_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $favoritedLessons = $this->lessonFavoriteRepository->findBy([
            'user' => $this->getUser()
        ]);

        // teachable lessons
        $teachableLessons = $this->lessonTeachableRepository->findBy([
            'user' => $this->getUser()
        ]);

        $user = $this->getUser();
        return $this->render('lesson/index.html.twig', [
            'user' => $user,
            'favoritedLessons' => $favoritedLessons,
            'teachableLessons' => $teachableLessons
        ]);
    }

    /**
     * @Route("/lessons/new", name="lesson_new", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request) {

        $user = $this->getUser();
        $lesson = new Lesson();

        $options = [
            'method' => 'POST',
            'skip_validation' => $request->request->get('skip_validation', false)
        ];

        $form = $this->createForm(NewLessonType::class, $lesson, $options);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Lesson $lesson */
            $lesson = $form->getData();

            $lesson->setUser($user);
            $this->entityManager->persist($lesson);

            $teachableLesson = new LessonTeachable();
            $teachableLesson->setLesson($lesson);
            $teachableLesson->setUser($user);
            $this->entityManager->persist($teachableLesson);

            $this->entityManager->flush();

            $this->addFlash('success', 'Lesson successfully created');
            return $this->redirectToRoute('lesson_index');
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

        return $this->render('lesson/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/lessons/{id}/view", name="lesson_view", options = { "expose" = true })
     * @param Request $request
     * @param Lesson $lesson
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Lesson $lesson) {

        $user = $this->getUser();

        return $this->render('lesson/view.html.twig', [
            'user' => $user,
            'lesson' => $lesson
        ]);
    }

    /**
     * @Route("/lessons/{id}/edit", name="lesson_edit", options = { "expose" = true })
     * @param Request $request
     * @param Lesson $lesson
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Lesson $lesson) {

        $this->denyAccessUnlessGranted('edit', $lesson);

        $user = $this->getUser();

        $options = [
            'method' => 'POST',
            'skip_validation' => $request->request->get('skip_validation', false)
        ];

        $form = $this->createForm(EditLessonType::class, $lesson, $options);

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()) {
            /** @var Lesson $lesson */
            $lesson = $form->getData();

            $uploadedFile = $form->get('thumbnailImage')->getData();

            if($uploadedFile) {
                $newFilename = $this->uploaderHelper->upload($uploadedFile, UploaderHelper::LESSON_THUMBNAIL);
                $lesson->setThumbnailImage($newFilename);

                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_THUMBNAIL) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            $uploadedFile = $form->get('featuredImage')->getData();

            if($uploadedFile) {
                $newFilename = $this->uploaderHelper->upload($uploadedFile, UploaderHelper::LESSON_FEATURED);
                $lesson->setFeaturedImage($newFilename);
            }

            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $this->addFlash('success', 'Lesson successfully updated');
            return $this->redirectToRoute('lesson_edit', ['id' => $lesson->getId()]);

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

        return $this->render('lesson/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'lesson' => $lesson
        ]);
    }

    /**
     * @Route("/lessons/{id}/delete", name="lesson_delete", options = { "expose" = true })
     * @param Lesson $lesson
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteLessonAction(Lesson $lesson, Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $canDelete = $user->isAdmin() || ($lesson->getUser() && $lesson->getUser()->getId() === $user->getId());

        if($canDelete) {
            $this->entityManager->remove($lesson);
            $this->entityManager->flush();

            $this->addFlash('success', 'lesson deleted');
        } else {
            $this->addFlash('error', 'lesson can not be deleted');
        }

        return $this->redirectToRoute('lesson_index');
    }

    /**
     * @Route("/lessons/{id}/thumbnail/add", name="lesson_thumbnail_add", options = { "expose" = true })
     * @param Request $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function lessonAddThumbnailAction(Request $request, Lesson $lesson) {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $thumbnailImage = $request->files->get('file');

        if($thumbnailImage) {
            $newFilename = $this->uploaderHelper->upload($thumbnailImage, UploaderHelper::LESSON_THUMBNAIL);
            $lesson->setThumbnailImage($newFilename);
            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_THUMBNAIL) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::LESSON_THUMBNAIL.'/'.$newFilename, 'squared_thumbnail_small')
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => true,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/lessons/{id}/featured/add", name="lesson_featured_add", options = { "expose" = true })
     * @param Request $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function lessonAddFeaturedAction(Request $request, Lesson $lesson) {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $featuredImage = $request->files->get('file');

        if($featuredImage) {
            $newFilename = $this->uploaderHelper->upload($featuredImage, UploaderHelper::LESSON_FEATURED);
            $lesson->setFeaturedImage($newFilename);
            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_FEATURED) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::LESSON_FEATURED.'/'.$newFilename, 'squared_thumbnail_small')
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
     * @Route("/lessons/{id}/resource/add", name="lesson_resource_add", options = { "expose" = true })
     * @param Request $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function lessonAddResourceAction(Request $request, Lesson $lesson) {

        /** @var UploadedFile $file */
        $file = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($file && $title && $description) {
            $mimeType = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::LESSON_RESOURCE);
            $lessonResource = new LessonResource();

            $lessonResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $lessonResource->setMimeType($mimeType ?? 'application/octet-stream');
            $lessonResource->setFileName($newFilename);
            $lessonResource->setFile(null);
            $lessonResource->setLesson($lesson);
            $lessonResource->setDescription($description);
            $lessonResource->setTitle($title);
            $this->entityManager->persist($lessonResource);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => 'uploads/'.UploaderHelper::LESSON_RESOURCE.'/'.$newFilename,
                    'resourceId' => $lessonResource->getId()

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
     * @Route("/lessons/{lesson_id}/resource/{resource_id}/remove", name="lesson_resource_remove", options = { "expose" = true })
     * @ParamConverter("lesson", options={"id" = "lesson_id"})
     * @ParamConverter("lessonResource", options={"id" = "resource_id"})
     * @param Request $request
     * @param Lesson $lesson
     * @param LessonResource $lessonResource
     * @return JsonResponse
     */
    public function lessonRemoveResourceAction(Request $request, Lesson $lesson, LessonResource $lessonResource) {

        $this->denyAccessUnlessGranted('edit', $lesson);

        if($lesson->getId() !== $lessonResource->getLesson()->getId()) {
            throw new NotFoundHttpException();
        }

        $this->entityManager->remove($lessonResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }
}