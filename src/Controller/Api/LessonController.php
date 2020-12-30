<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonFavorite;
use App\Entity\LessonTeachable;
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
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonTeachableRepository;
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
 * Class LessonController
 * @package App\Controller
 * @Route("/api")
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
     * @var LessonRepository
     */
    private $lessonRepository;

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
     * @param SerializerInterface $serializer
     * @param CompanyRepository $companyRepository
     * @param IndustryRepository $industryRepository
     * @param LessonRepository $lessonRepository
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
        SerializerInterface $serializer,
        CompanyRepository $companyRepository,
        IndustryRepository $industryRepository,
        LessonRepository $lessonRepository,
        LessonFavoriteRepository $lessonFavoriteRepository,
        LessonTeachableRepository $lessonTeachableRepository
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
        $this->lessonRepository = $lessonRepository;
        $this->lessonFavoriteRepository = $lessonFavoriteRepository;
        $this->lessonTeachableRepository = $lessonTeachableRepository;
    }


    /**
     * @Route("/lessons", name="get_lessons", methods={"GET"}, options = { "expose" = true })
     */
    public function getLessons() {

        $lessons = $this->lessonRepository->findAll(['title' => 'asc']);

        $user = $this->getUser();

        /** @var Lesson $lesson */
        foreach($lessons as $lesson) {

            // let's go ahead and be crazy here and add whether or not this lesson is a logged in use favorite
            $favoriteLesson = $this->lessonFavoriteRepository->findOneBy([
                'lesson' => $lesson,
                'user' => $user
            ]);

            if($favoriteLesson) {
                $lesson->setIsFavorite(true);
            } else {
                $lesson->setIsFavorite(false);
            }

            // let's be even more crazy and add whether or not it's a teachable lesson
            $teachableLesson = $this->lessonTeachableRepository->findOneBy([
                'lesson' => $lesson,
                'user' => $user
            ]);

            if($teachableLesson) {
                $lesson->setIsTeachable(true);
            } else {
                $lesson->setIsTeachable(false);
            }

            // Add whether or not the lesson has presenters available
            $presenters = $this->lessonTeachableRepository->findByExpertPresenters([
                'lesson' => $lesson->getId()
            ]);

            if($presenters) {
                $lesson->setHasExpertPresenters(true);
            } else {
                $lesson->setHasExpertPresenters(false);
            }


            // Add whether or not the lesson has educators who requested this course be taught
            $requestors = $this->lessonTeachableRepository->findByEducatorRequestors([
                'lesson' => $lesson->getId()
            ]);

            if($requestors) {
                $lesson->setHasEducatorRequestors(true);
            } else {
                $lesson->setHasEducatorRequestors(false);
            }
        }

        $json = $this->serializer->serialize($lessons, 'json', ['groups' => ['LESSON_DATA']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/lessons/{id}/teach", name="teach_lesson", methods={"POST"}, options = { "expose" = true })
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function teachLesson(Lesson $lesson) {

        $lessonObj = $this->lessonTeachableRepository->findOneBy([
            'user' => $this->getUser(),
            'lesson' => $lesson,
        ]);

        if($lessonObj) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'lesson has already been added as a teachable lesson.',

                ],
                Response::HTTP_OK
            );
        }

        $lessonTeachable = new LessonTeachable();
        $lessonTeachable->setUser($this->getUser());
        $lessonTeachable->setLesson($lesson);

        $this->entityManager->persist($lessonTeachable);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/lessons/{id}/unteach", name="unteach_lesson", methods={"POST"}, options = { "expose" = true })
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function unteachLesson(Lesson $lesson) {

        $lessonObj = $this->lessonTeachableRepository->findOneBy([
            'user' => $this->getUser(),
            'lesson' => $lesson,
        ]);

        if($lessonObj) {
            $this->entityManager->remove($lessonObj);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'message' => 'lesson removed from teachable lessons.',
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
                'message' => 'lesson cannot be removed from teachable lessons cause it does not exist in the teachable lessons for this user.',
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/lessons/{id}/favorite", name="favorite_lesson", methods={"POST"}, options = { "expose" = true })
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function favoriteLesson(Lesson $lesson) {

        $lessonObj = $this->lessonFavoriteRepository->findOneBy([
            'user' => $this->getUser(),
            'lesson' => $lesson,
        ]);

        if($lessonObj) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'lesson has already been added to favorites.',

                ],
                Response::HTTP_OK
            );
        }

        $lessonFavorite = new LessonFavorite();
        $lessonFavorite->setUser($this->getUser());
        $lessonFavorite->setLesson($lesson);

        $this->entityManager->persist($lessonFavorite);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/lessons/{id}/unfavorite", name="unfavorite_lesson", methods={"POST"}, options = { "expose" = true })
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function unFavoriteLesson(Lesson $lesson) {

        $lessonObj = $this->lessonFavoriteRepository->findOneBy([
            'user' => $this->getUser(),
            'lesson' => $lesson,
        ]);

        if($lessonObj) {
           $this->entityManager->remove($lessonObj);
           $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'message' => 'lesson removed from favorites',
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'lesson cannot be removed from favorites cause it does not exist in favorites',
            ],
            Response::HTTP_OK
        );
    }
    /**
     * @Route("/lessons/mine", name="lessons_mine", methods={"GET"}, options = { "expose" = true })
     * @return JsonResponse
     */
    public function myLessons() {

        $lessons = $this->lessonRepository->findBy(
            [
                'user' => $this->getUser(),
            ]
        );

        $json = $this->serializer->serialize($lessons, 'json', ['groups' => ['LESSON_DATA']]);

        $payload = json_decode($json, true);


        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/lessons/favorites", name="get_favorite_lessons", methods={"GET"}, options = { "expose" = true })
     * @return JsonResponse
     */
    public function getFavoriteLessons() {

        $favorites = $this->lessonFavoriteRepository->findBy(
            [
                'user' => $this->getUser(),
            ]
        );

        $json = $this->serializer->serialize($favorites, 'json', ['groups' => ['LESSON_DATA']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }
}