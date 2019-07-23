<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
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
        LessonTeachableRepository $lessonTeachableRepository
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
        $form = $this->createForm(NewLessonType::class, $lesson, [
            'method' => 'POST'
        ]);

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

            $lesson->setUser($user);
            $this->entityManager->persist($lesson);

            $teachableLesson = new LessonTeachable();
            $teachableLesson->setLesson($lesson);
            $teachableLesson->setUser($user);
            $this->entityManager->persist($teachableLesson);

            $this->entityManager->flush();

            return $this->redirectToRoute('lesson_index');
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

        return $this->render('lesson/view.html.twig', [
            'user' => $this->getUser(),
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
        $form = $this->createForm(NewLessonType::class, $lesson, [
            'method' => 'POST'
        ]);

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
        }

        return $this->render('lesson/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'lesson' => $lesson
        ]);
    }
}