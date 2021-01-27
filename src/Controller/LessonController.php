<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonResource;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EditLessonType;
use App\Form\Filter\LessonFilterType;
use App\Form\Filter\ProfessionalFilterType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Message\RecapMessage;
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
 *
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        $favoritedLessons = $this->lessonFavoriteRepository->findBy(
            [
                'user' => $this->getUser(),
            ]
        );

        // teachable lessons
        $teachableLessons = $this->lessonTeachableRepository->findBy(
            [
                'user' => $this->getUser(),
            ]
        );

        $user = $this->getUser();

        return $this->render(
            'lesson/index.html.twig', [
                                        'user'             => $user,
                                        'favoritedLessons' => $favoritedLessons,
                                        'teachableLessons' => $teachableLessons,
                                    ]
        );
    }

    /**
     * @Route("/lessons/results", name="lessons_results_page", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resultsPageAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $favoritedLessons = $this->lessonFavoriteRepository->findBy(
            [
                'user' => $this->getUser(),
            ]
        );

        // teachable lessons
        $teachableLessons = $this->lessonTeachableRepository->findBy(
            [
                'user' => $this->getUser(),
            ]
        );

        $form = $this->createForm(
            LessonFilterType::class, null, [
                                       'method' => 'GET',
                                   ]
        );

        $form->handleRequest($request);

        $filterBuilder = $this->lessonRepository->createQueryBuilder('l')
                                                ->addOrderBy('l.title', 'ASC');

        // TODO LOOK AT API/LESSONCONTROLLER.PHP TO SEE IF YOU NEED TO MODIFY THE QUERY

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $sql = $filterQuery->getSQL();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render(
            'lesson/results.html.twig', [
                                          'favoritedLessons' => $favoritedLessons,
                                          'teachableLessons' => $teachableLessons,
                                          'user'             => $user,
                                          'pagination'       => $pagination,
                                          'form'             => $form->createView(),
                                          'clearFormUrl'     => $this->generateUrl('lessons_results_page'),
                                      ]
        );
    }

    /**
     * @Route("/lessons/teachable", name="lessons_teachable_page", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teachableAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        // teachable lessons
        $lessons = $this->lessonTeachableRepository->findBy(
            [
                'user' => $this->getUser(),
            ]
        );

        $form = $this->createForm(
            LessonFilterType::class, null, [
                                       'method' => 'GET',
                                   ]
        );

        $form->handleRequest($request);

        $filterBuilder = $this->lessonRepository->createQueryBuilder('l')
                                                ->innerJoin('l.lessonTeachables', 'lt')
                                                ->innerJoin('lt.user', 'u')
                                                ->andWhere('u.id = :userId')
                                                ->setParameter('userId', $user->getId())
                                                ->addOrderBy('l.title', 'ASC');

        // TODO LOOK AT API/LESSONCONTROLLER.PHP TO SEE IF YOU NEED TO MODIFY THE QUERY

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $sql = $filterQuery->getSQL();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render(
            'lesson/teachable.html.twig', [
                                            'user'         => $user,
                                            'pagination'   => $pagination,
                                            'form'         => $form->createView(),
                                            'clearFormUrl' => $this->generateUrl('lessons_results_page'),
                                        ]
        );
    }

    /**
     * @Route("/lessons/my-created", name="lessons_my_created", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function myCreatedAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(
            LessonFilterType::class, null, [
                                       'method' => 'GET',
                                   ]
        );

        $form->handleRequest($request);

        $filterBuilder = $this->lessonRepository->createQueryBuilder('l')
                                                ->andWhere('l.user = :user')
                                                ->setParameter('user', $user)
                                                ->addOrderBy('l.title', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $sql = $filterQuery->getSQL();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );


        return $this->render(
            'lesson/my_created.html.twig', [
                                             'user'         => $user,
                                             'pagination'   => $pagination,
                                             'form'         => $form->createView(),
                                             'clearFormUrl' => $this->generateUrl('lessons_my_created'),
                                         ]
        );
    }

    /**
     * @Route("/lessons/new", name="lesson_new", options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {

        $user   = $this->getUser();
        $lesson = new Lesson();

        $options = [
            'method'          => 'POST',
            'skip_validation' => $request->request->get('skip_validation', false),
        ];

        $form = $this->createForm(NewLessonType::class, $lesson, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

            if ($request->request->get('add_resource') == 'Yes') {
                return $this->redirectToRoute('lesson_edit', ['id' => $lesson->getId(), 'tab' => 'resources']);
            } else {
                return $this->redirectToRoute('lessons_results_page');
            }

        }

        return $this->render(
            'lesson/new.html.twig', [
                                      'user' => $user,
                                      'form' => $form->createView(),
                                  ]
        );
    }

    /**
     * @Route("/lessons/{id}/view", name="lesson_view", options = { "expose" = true })
     * @param Request $request
     * @param Lesson  $lesson
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Lesson $lesson)
    {

        /** @var User $user */
        $user              = $this->getUser();
        $primaryIndustries = [];

        if (sizeof($lesson->getSecondaryIndustries()) > 0) {
            foreach ($lesson->getSecondaryIndustries() as $secondaryIndustry) {
                if (!in_array($secondaryIndustry->getPrimaryIndustry()->getId(), $primaryIndustries)) {
                    $primaryIndustries[$secondaryIndustry->getPrimaryIndustry()->getId()] = $secondaryIndustry->getPrimaryIndustry()->getName();
                }
            }
        }

        $lessonTeachables = $this->lessonTeachableRepository->findBy(
            [
                'lesson' => $lesson,
            ]
        );

        if ($user->isEducator()) {
            /** @var EducatorUser $user */

            $lessonTeachables = array_filter(
                $lessonTeachables, function (LessonTeachable $lessonTeachable) use ($user) {

                $lessonCreator = $lessonTeachable->getUser();

                if (!$lessonCreator instanceof ProfessionalUser) {
                    return false;
                }

                foreach ($lessonCreator->getRegions() as $region) {
                    if ($region->getId() === $user->getSchool()->getRegion()->getId()) {
                        return true;
                    }
                }

                return false;

            }
            );
        }

        return $this->render(
            'lesson/view.html.twig', [
                                       'user'               => $user,
                                       'lesson'             => $lesson,
                                       'primary_industries' => $primaryIndustries,
                                       'lessonTeachables'   => $lessonTeachables,
                                   ]
        );
    }

    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/lessons/{lesson_id}/users/{professional_id}/requests/teach", name="lesson_request_to_teach", options = { "expose" = true }, methods={"POST"})
     * @ParamConverter("lesson", options={"id" = "lesson_id"})
     * @ParamConverter("professionalUser", options={"id" = "professional_id"})
     * @param Request          $request
     * @param Lesson           $lesson
     * @param ProfessionalUser $professionalUser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function requestToTeachAction(Request $request, Lesson $lesson, ProfessionalUser $professionalUser)
    {

        /** @var User $user */
        $user = $this->getUser();

        $dateOptionOne      = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('dateOptionOne'));
        $dateOptionTwo      = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('dateOptionTwo'));
        $dateOptionThree    = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('dateOptionThree'));
        $teachLessonRequest = new TeachLessonRequest();
        $teachLessonRequest->setDateOptionOne($dateOptionOne);
        $teachLessonRequest->setDateOptionTwo($dateOptionTwo);
        $teachLessonRequest->setDateOptionThree($dateOptionThree);
        $teachLessonRequest->setLesson($lesson);
        $teachLessonRequest->setCreatedBy($user);
        $teachLessonRequest->setNeedsApprovalBy($professionalUser);
        $teachLessonRequest->setSchool($user->getSchool());
        $this->entityManager->persist($teachLessonRequest);
        $this->entityManager->flush();

        $this->requestsMailer->teachLessonRequest($teachLessonRequest);

        $this->addFlash('success', 'Request successfully sent!');

        /*   if($redirectUrl) {
               return $this->redirect($redirectUrl);
           }*/

        return $this->redirectToRoute('lesson_view', ['id' => $lesson->getId()]);
    }

    /**
     * @Route("/lessons/{id}/edit", name="lesson_edit", options = { "expose" = true })
     * @param Request $request
     * @param Lesson  $lesson
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Lesson $lesson)
    {

        $this->denyAccessUnlessGranted('edit', $lesson);

        $user = $this->getUser();

        $options = [
            'method'          => 'POST',
            'skip_validation' => $request->request->get('skip_validation', false),
        ];

        $form = $this->createForm(EditLessonType::class, $lesson, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Lesson $lesson */
            $lesson = $form->getData();

            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $this->addFlash('success', 'Lesson successfully updated');

            return $this->redirectToRoute('lesson_edit', ['id' => $lesson->getId()]);

        }

        return $this->render(
            'lesson/edit.html.twig', [
                                       'user'   => $user,
                                       'form'   => $form->createView(),
                                       'lesson' => $lesson,
                                   ]
        );
    }

    /**
     * @Route("/lessons/{id}/delete", name="lesson_delete", options = { "expose" = true })
     * @param Lesson  $lesson
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteLessonAction(Lesson $lesson, Request $request)
    {

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
     * @param Lesson  $lesson
     *
     * @return JsonResponse
     */
    public function lessonAddThumbnailAction(Request $request, Lesson $lesson)
    {

        $this->denyAccessUnlessGranted('edit', $lesson);

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $thumbnailImage = $request->files->get('file');

        if ($thumbnailImage) {
            $newFilename = $this->uploaderHelper->upload($thumbnailImage, UploaderHelper::LESSON_THUMBNAIL);
            $lesson->setThumbnailImage($newFilename);
            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_THUMBNAIL) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            return new JsonResponse(
                [
                    'success' => true,
                    'url'     => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::LESSON_THUMBNAIL . '/' . $newFilename, 'squared_thumbnail_small'),
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
     * @param Lesson  $lesson
     *
     * @return JsonResponse
     */
    public function lessonAddFeaturedAction(Request $request, Lesson $lesson)
    {

        $this->denyAccessUnlessGranted('edit', $lesson);

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $featuredImage = $request->files->get('file');

        if ($featuredImage) {
            $newFilename = $this->uploaderHelper->upload($featuredImage, UploaderHelper::LESSON_FEATURED);
            $lesson->setFeaturedImage($newFilename);
            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_FEATURED) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            return new JsonResponse(
                [
                    'success' => true,
                    'url'     => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::LESSON_FEATURED . '/' . $newFilename, 'squared_thumbnail_small'),
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
     * @param Lesson  $lesson
     *
     * @return JsonResponse
     */
    public function lessonAddResourceAction(Request $request, Lesson $lesson)
    {

        $this->denyAccessUnlessGranted('edit', $lesson);

        /** @var UploadedFile $file */
        $file          = $request->files->get('resource');
        $title         = $request->request->get('title');
        $description   = $request->request->get('description');
        $linkToWebsite = $request->request->get('linkToWebsite');

        if ($file && $title) {
            $mimeType       = $file->getMimeType();
            $newFilename    = $this->uploaderHelper->upload($file, UploaderHelper::LESSON_RESOURCE);
            $lessonResource = new LessonResource();

            $lessonResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $lessonResource->setMimeType($mimeType ?? 'application/octet-stream');
            $lessonResource->setFileName($newFilename);
            $lessonResource->setFile(null);
            $lessonResource->setLesson($lesson);
            $lessonResource->setDescription($description ? $description : null);
            $lessonResource->setTitle($title);
            $this->entityManager->persist($lessonResource);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success'     => true,
                    'url'         => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::LESSON_RESOURCE . '/' . $newFilename,
                    'id'          => $lessonResource->getId(),
                    'title'       => $title,
                    'description' => $description,

                ], Response::HTTP_OK
            );
        } else {
            if ($linkToWebsite && $title) {
                $mimeType       = "";
                $newFilename    = "";
                $lessonResource = new LessonResource();

                $lessonResource->setOriginalName($newFilename);
                $lessonResource->setMimeType($mimeType);
                $lessonResource->setFileName($newFilename);
                $lessonResource->setFile(null);
                $lessonResource->setLesson($lesson);
                $lessonResource->setDescription($description ? $description : null);
                $lessonResource->setTitle($title);
                $lessonResource->setLinkToWebsite($linkToWebsite);
                $this->entityManager->persist($lessonResource);
                $this->entityManager->flush();

                return new JsonResponse(
                    [
                        'success'     => true,
                        'url'         => $linkToWebsite,
                        'id'          => $lessonResource->getId(),
                        'title'       => $title,
                        'description' => $description,

                    ], Response::HTTP_OK
                );
            }
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/lessons/file/{id}/get", name="lesson_file_get", options = { "expose" = true })
     * @param Request        $request
     * @param LessonResource $file
     *
     * @return JsonResponse
     */
    public function lessonGetFileAction(Request $request, LessonResource $file)
    {
        $this->denyAccessUnlessGranted('edit', $file->getLesson());


        if ($file->getFile() != null) {
            return new JsonResponse(
                [
                    'success'     => true,
                    'url'         => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::EXPERIENCE_FILE . '/' . $file->getFileName(),
                    'id'          => $file->getId(),
                    'title'       => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success'     => true,
                    'website'     => $file->getLinkToWebsite(),
                    'id'          => $file->getId(),
                    'title'       => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        }
    }

    /**
     * @Route("/lessons/file/{id}/edit", name="lesson_file_edit", options = { "expose" = true })
     * @param Request        $request
     * @param LessonResource $file
     *
     * @return JsonResponse
     */
    public function lessonEditFileAction(Request $request, LessonResource $file)
    {

        $this->denyAccessUnlessGranted('edit', $file->getLesson());

        /** @var UploadedFile $resource */
        $resource      = $request->files->get('resource');
        $title         = $request->request->get('title');
        $description   = $request->request->get('description');
        $linkToWebsite = $request->request->get('linkToWebsite');

        if ($title) {
            $file->setTitle($title);
        }

        if ($description) {
            $file->setDescription($description);
        }

        if ($linkToWebsite && $linkToWebsite != "http://") {
            $file->setLinkToWebsite($linkToWebsite);
        } else {
            $file->setLinkToWebsite(null);
        }


        if ($resource) {
            $mimeType    = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::LESSON_RESOURCE);

            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
        } else {
            $file->setOriginalName(null);
            $file->setMimeType(null);
            $file->setFileName(null);
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();


        if ($file->getFileName() != null) {
            return new JsonResponse(
                [
                    'success'     => true,
                    'url'         => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::LESSON_RESOURCE . '/' . $file->getFileName(),
                    'id'          => $file->getId(),
                    'title'       => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success'     => true,
                    'url'         => $file->getLinkToWebsite(),
                    'id'          => $file->getId(),
                    'title'       => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        }
    }


    /**
     * @Route("/lessons/resources/{id}/edit", name="lesson_resource_edit", options = { "expose" = true })
     * @param Request        $request
     * @param LessonResource $lessonResource
     *
     * @return JsonResponse
     */
    public function lessonEditResourceAction(Request $request, LessonResource $lessonResource)
    {

        $this->denyAccessUnlessGranted('edit', $lessonResource->getLesson());

        /** @var UploadedFile $file */
        $file        = $request->files->get('resource');
        $title       = $request->request->get('title');
        $description = $request->request->get('description');

        if ($file && $title && $description) {
            $mimeType    = $file->getMimeType();
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
                    'success'     => true,
                    'url'         => 'uploads/' . UploaderHelper::LESSON_RESOURCE . '/' . $newFilename,
                    'id'          => $lessonResource->getId(),
                    'title'       => $title,
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
     * @Route("/lessons/resources/{id}/remove", name="lesson_resource_remove", options = { "expose" = true })
     * @param Request        $request
     * @param LessonResource $lessonResource
     *
     * @return JsonResponse
     */
    public function lessonRemoveResourceAction(Request $request, LessonResource $lessonResource)
    {

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