<?php

namespace App\Controller;

use App\Entity\EducatorUser;
use App\Entity\Lesson;
use App\Entity\LessonResource;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RequestAction;
use App\Entity\RequestPossibleApprovers;
use App\Entity\User;
use App\Form\EditLessonType;
use App\Form\Filter\LessonFilterType;
use App\Form\LessonType;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        // pretty sure this function is deprecated so let's redirect to the new results page
        return $this->redirectToRoute('lessons_results_page');

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
                'user' => $user,
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
                                                ->andWhere('l.deleted = :deleted')
                                                ->setParameter('deleted', false)
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
                'user' => $user,
                'pagination' => $pagination,
                'form' => $form->createView(),
                'clearFormUrl' => $this->generateUrl('lessons_results_page'),
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
                'user' => $user,
                'pagination' => $pagination,
                'form' => $form->createView(),
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
                'user' => $user,
                'pagination' => $pagination,
                'form' => $form->createView(),
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

        $form = $this->createForm(LessonType::class, $lesson);

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

            $this->addFlash('success', 'Topic Presentation successfully created. Please add any additional resources or attachments needed for this lesson.');

            return $this->redirectToRoute('lesson_edit', ['id' => $lesson->getId()]);
        }

        return $this->render(
            'lesson/new.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
                'lesson' => $lesson
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

        foreach($lesson->getPrimaryIndustries() as $primaryIndustry) {
            $primaryIndustries[$primaryIndustry->getId()] = $primaryIndustry->getName();
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
                'user' => $user,
                'lesson' => $lesson,
                'primary_industries' => $primaryIndustries,
                'lessonTeachables' => $lessonTeachables,
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
     * @throws \Exception
     */
    public function requestToTeachAction(Request $request, Lesson $lesson, ProfessionalUser $professionalUser)
    {

        /** @var EducatorUser $user */
        $user            = $this->getUser();
        $school          = $user->getSchool();
        $dateOptionOne   = $request->request->get('dateOptionOne');
        $dateOptionTwo   = $request->request->get('dateOptionTwo');
        $dateOptionThree = $request->request->get('dateOptionThree');

        $teachLessonRequest = new \App\Entity\Request();
        $teachLessonRequest->setRequestType(\App\Entity\Request::REQUEST_TYPE_TEACH_LESSON_INVITE);
        $teachLessonRequest->setCreatedBy($user);
        $teachLessonRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING);
        $teachLessonRequest->setStatusLabel('Guest instructor invite is pending approval');
        $teachLessonRequest->setNotification([
            'title' => "<strong>{$user->getFullName()}</strong> has invited you to guest instruct \"{$lesson->getTitle()}\"",
            'data' => [
                'professional_id' => $professionalUser->getId(),
                'school_id' => $school->getId(),
                'educator_id' => $user->getId(),
            ],
            'user_photo' => $user->getPhotoPath(),
            'user_photos' => [
                [
                    'order' => 1,
                    'path' => $user->getPhotoPath(),
                ],
                [
                    'order' => 2,
                    'path' => $professionalUser->getPhotoPath(),
                ],
            ],
            'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
            'suggested_dates' => [
                'date_option_one' => $dateOptionOne,
                'date_option_two' => $dateOptionTwo,
                'date_option_three' => $dateOptionThree,
            ],
            'messages' => [],
            'body' => [
                'Request Type' => [
                    'order' => 1,
                    'value' => 'Guest Instructor Invite',
                ],
                'Initiated By' => [
                    'order' => 2,
                    'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $user->getId()])}'>{$user->getFullName()}</a>",
                ],
                'Sent To' => [
                    'order' => 3,
                    'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $professionalUser->getId()])}'>{$professionalUser->getFullName()}</a>",
                ],
                'School Name' => [
                    'order' => 4,
                    'value' => "<a target='_blank' href='{$this->generateUrl('school_view', ['id' => $school->getId()])}'>{$school->getName()}</a>",
                ],
                'Selected Date' => [
                    'order' => 5,
                    'value' => "To be determined",
                ],
                'Suggested Dates' => [
                    'order' => 6,
                    'value' => "{$dateOptionOne} <br> {$dateOptionTwo} <br> {$dateOptionThree}",
                ],
                'Created On' => [
                    'order' => 7,
                    'value' => (new \DateTime())->format("m/d/Y h:i A"),
                ],
            ],
        ]);

        $createdByApprover = new RequestPossibleApprovers();
        $createdByApprover->setPossibleApprover($user);
        $createdByApprover->setRequest($teachLessonRequest);
        $createdByApprover->setNotificationDate(new \DateTime());
        $createdByApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
        ]);
        $createdByApprover->setNotificationTitle("<strong>{$professionalUser->getFullName()}</strong> has been invited by you to guest instruct \"{$lesson->getTitle()}\"");

        $possibleApprover = new RequestPossibleApprovers();
        $possibleApprover->setPossibleApprover($professionalUser);
        $possibleApprover->setRequest($teachLessonRequest);
        $possibleApprover->setHasNotification(true);
        $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_APPROVE,
                                               RequestAction::REQUEST_ACTION_NAME_DENY,
                                               RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                                               RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                                               RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
        ]);
        $possibleApprover->setNotificationTitle("<strong>{$user->getFullName()}</strong> has invited you to guest instruct \"{$lesson->getTitle()}\"");


        $this->entityManager->persist($createdByApprover);
        $this->entityManager->persist($possibleApprover);
        $this->entityManager->persist($teachLessonRequest);
        $this->entityManager->flush();

        $requestActionUrl = $this->generateUrl('request_action', [
            'lesson_id' => $lesson->getId(),
            'request_id' => $teachLessonRequest->getId(),
        ]);

        $teachLessonRequest->setActionUrl($requestActionUrl);

        $this->entityManager->flush();
        $this->entityManager->refresh($teachLessonRequest);

        $this->requestsMailer->teachLessonInviteApproval($professionalUser, $user, $lesson);

        $this->addFlash('success', 'Request successfully sent!');

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

        $form = $this->createForm(LessonType::class, $lesson);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Lesson $lesson */
            $lesson = $form->getData();

            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $this->addFlash('success', 'Topic Presentation successfully updated');

            return $this->redirectToRoute('lesson_edit', ['id' => $lesson->getId()]);

        }

        return $this->render(
            'lesson/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
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

        $lesson->setDeleted(true);
        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->addFlash('success', 'Topic deleted');

        return $this->redirectToRoute('lessons_results_page');
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
                    'url' => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::LESSON_THUMBNAIL . '/' . $newFilename, 'squared_thumbnail_small'),
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
                    'url' => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::LESSON_FEATURED . '/' . $newFilename, 'squared_thumbnail_small'),
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
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::EXPERIENCE_FILE . '/' . $file->getFileName(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success' => true,
                    'website' => $file->getLinkToWebsite(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
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
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::LESSON_RESOURCE . '/' . $file->getFileName(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $file->getLinkToWebsite(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        }
    }

}