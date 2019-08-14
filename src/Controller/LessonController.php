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
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EditLessonType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateTime;
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
    use ServiceHelper;

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
            return $this->redirectToRoute('lesson_edit', ['id' => $lesson->getId() ]);
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
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/lessons/{lesson_id}/users/{professional_id}/requests/teach", name="lesson_request_to_teach", options = { "expose" = true }, methods={"POST"})
     * @ParamConverter("lesson", options={"id" = "lesson_id"})
     * @ParamConverter("professionalUser", options={"id" = "professional_id"})
     * @param Request $request
     * @param Lesson $lesson
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function requestToTeachAction(Request $request, Lesson $lesson, ProfessionalUser $professionalUser) {

        /** @var User $user */
        $user = $this->getUser();

        $dateOptionOne = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('dateOptionOne'));
        $dateOptionTwo = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('dateOptionTwo'));
        $dateOptionThree = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('dateOptionThree'));
        $redirectUrl = $request->request->get('redirectUrl', null);

        $requests = $this->teachLessonRequestRepository->getByEducatorAndProfessional($user, $professionalUser);

        if(count($requests) > 0) {
            $this->addFlash('error', 'You have already made a request to this professional to teach this lesson.');
            if($redirectUrl) {
                return $this->redirect($redirectUrl);
            }
            return $this->redirectToRoute('lesson_view', ['id' => $lesson->getId()]);
        }

        $teachLessonRequest = new TeachLessonRequest();
        $teachLessonRequest->setDateOptionOne($dateOptionOne);
        $teachLessonRequest->setDateOptionTwo($dateOptionTwo);
        $teachLessonRequest->setDateOptionThree($dateOptionThree);
        $teachLessonRequest->setLesson($lesson);
        $teachLessonRequest->setCreatedBy($user);
        $teachLessonRequest->setNeedsApprovalBy($professionalUser);
        $this->entityManager->persist($teachLessonRequest);
        $this->entityManager->flush();

        $this->requestsMailer->teachLessonRequest($teachLessonRequest);

        $this->addFlash('success', 'Request successfully sent!');

        if($redirectUrl) {
            return $this->redirect($redirectUrl);
        }

        return $this->redirectToRoute('lesson_view', ['id' => $lesson->getId()]);
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

        $this->denyAccessUnlessGranted('edit', $lesson);

        /** @var User $user */
        $user = $this->getUser();

        $this->entityManager->remove($lesson);
        $this->entityManager->flush();

        $this->addFlash('success', 'lesson deleted');

        return $this->redirectToRoute('lesson_index');
    }

    /**
     * @Route("/lessons/{id}/thumbnail/add", name="lesson_thumbnail_add", options = { "expose" = true })
     * @param Request $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function lessonAddThumbnailAction(Request $request, Lesson $lesson) {

        $this->denyAccessUnlessGranted('edit', $lesson);

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
                'success' => false,
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

        $this->denyAccessUnlessGranted('edit', $lesson);

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

        $this->denyAccessUnlessGranted('edit', $lesson);

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
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::LESSON_RESOURCE.'/'.$newFilename,
                    'id' => $lessonResource->getId(),
                    'title' => $title,
                    'description' => $description

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
     * @Route("/lessons/resources/{id}/edit", name="lesson_resource_edit", options = { "expose" = true })
     * @param Request $request
     * @param LessonResource $lessonResource
     * @return JsonResponse
     */
    public function lessonEditResourceAction(Request $request, LessonResource $lessonResource) {

        $this->denyAccessUnlessGranted('edit', $lessonResource->getLesson());

        /** @var UploadedFile $file */
        $file = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($file && $title && $description) {
            $mimeType = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::LESSON_RESOURCE);
            $lessonResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $lessonResource->setMimeType($mimeType ?? 'application/octet-stream');
            $lessonResource->setFileName($newFilename);
            $lessonResource->setFile(null);
            $lessonResource->setDescription($description);
            $lessonResource->setTitle($title);
            $this->entityManager->persist($lessonResource);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => 'uploads/'.UploaderHelper::LESSON_RESOURCE.'/'.$newFilename,
                    'id' => $lessonResource->getId(),
                    'title' => $title,
                    'description' => $description

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
     * @Route("/lessons/resources/{id}/remove", name="lesson_resource_remove", options = { "expose" = true })
     * @param Request $request
     * @param LessonResource $lessonResource
     * @return JsonResponse
     */
    public function lessonRemoveResourceAction(Request $request, LessonResource $lessonResource) {

        $this->denyAccessUnlessGranted('edit', $lessonResource->getLesson());

        $this->entityManager->remove($lessonResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }
}