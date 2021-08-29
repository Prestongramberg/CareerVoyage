<?php

namespace App\Controller;

use App\Entity\AllowedCommunication;
use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RequestAction;
use App\Entity\RequestPossibleApprovers;
use App\Entity\RolesWillingToFulfill;
use App\Entity\Registration;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Entity\UserMeta;
use App\Form\CreateRequestFormType;
use App\Form\EditRequestFormType;
use App\Form\Request\SelectSuggestedDatesFormType;
use App\Form\Request\SendMessageFormType;
use App\Form\Request\SuggestNewDatesFormType;
use App\Message\CreateJobBoardRequest;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateInterval;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Request as RequestEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Class RequestController
 *
 * @package App\Controller
 * @Route("/dashboard")
 */
class RequestController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/requests", name="requests", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $httpRequest
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requests(Request $httpRequest)
    {
        /** @var User $user */
        $user   = $this->getUser();
        $filter = $httpRequest->query->get('filter', null);

        if ($requestId = $httpRequest->query->get('id', null)) {

            $queryBuilder = $this->requestRepository->createQueryBuilder('r')
                                                    ->andWhere('r.id = :id')
                                                    ->setParameter('id', $requestId);

        } else {

            switch ($filter) {
                case 'created_by_me':
                    $queryBuilder = $this->requestRepository->getRequestsThatNeedMyApproval($user, false, null, true, $user);
                    break;

                case 'approved':
                    $queryBuilder = $this->requestRepository->getRequestsThatNeedMyApproval($user, false, null, true, $user, true);
                    break;

                case 'denied':
                    $queryBuilder = $this->requestRepository->getRequestsThatNeedMyApproval($user, false, null, true, $user, false, true);
                    break;

                case 'pending':
                    $queryBuilder = $this->requestRepository->getRequestsThatNeedMyApproval($user, false, null, true, $user, false, false, true);
                    break;
                default:
                    $queryBuilder = $this->requestRepository->getRequestsThatNeedMyApproval($user, false, null, true);
                    break;
            }
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder->getQuery(), /* query NOT result */
            $httpRequest->query->getInt('page', 1), /*page number*/
            10,
            ['distinct' => false]
        );

        return $this->render('request/index.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'filter' => $filter,
        ]);
    }

    /**
     * @Route("/requests/request-action", name="request_action", options = { "expose" = true })
     * @param Request $httpRequest
     *
     * @return Response
     */
    public function requestAction(Request $httpRequest)
    {
        /** @var User $loggedInUser */
        $loggedInUser         = $this->getUser();
        $requestId            = $httpRequest->query->get('request_id');
        $request              = $this->requestRepository->find($requestId);
        $action               = $httpRequest->query->get('action', null);
        $emailHandler         = function () {
        };
        $requestActionHandler = function () {
        };

        if (!$request) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        /** @var RequestPossibleApprovers $possibleApprover */
        if ($possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser)) {
            $possibleApprover->setHasNotification(false);
            $this->entityManager->flush();
        }

        $requestAction = new RequestAction();
        $requestAction->setUser($loggedInUser);
        $requestAction->setRequest($request);

        switch ($request->getRequestType()) {
            case \App\Entity\Request::REQUEST_TYPE_NEW_COMPANY:

                $companyId = $httpRequest->query->get('company_id');
                $company   = $this->companyRepository->find($companyId);
                $template  = 'request/modal/new_company.html.twig';

                $sendMessageForm = $this->createForm(SendMessageFormType::class, null, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);

                if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
                    $template = 'request/modal/send_message.html.twig';
                }

                $context = [
                    'request' => $request,
                    'company' => $company,
                    'loggedInUser' => $loggedInUser,
                    'sendMessageForm' => $sendMessageForm->createView(),
                ];

                $requestActionHandler = function () use (
                    $request, $company, $requestAction, $action, $loggedInUser, $sendMessageForm, $httpRequest, &
                    $template
                ) {

                    if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {
                        $company->setApproved(true);
                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                ->setStatusLabel('Company has been approved');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->requestsMailer->newCompanyApproved($company);

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_DENY) {
                        $company->setApproved(false);
                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_DENY);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_DENIED)
                                ->setStatusLabel('Company has been denied');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING) {
                        $company->setApproved(false);
                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                ->setStatusLabel('Company is pending approval');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {

                        $sendMessageForm->handleRequest($httpRequest);

                        if ($sendMessageForm->isSubmitted() && $sendMessageForm->isValid()) {

                            $formData     = $sendMessageForm->getData();
                            $notification = $request->getNotification();
                            $message      = $formData['message'];

                            $notification['messages'][] = [
                                'body' => $message,
                                'date' => (new \DateTime())->format('n/j/Y g:i A'),
                                'user' => [
                                    'id' => $loggedInUser->getId(),
                                    'full_name' => $loggedInUser->getFullName(),
                                    'photo' => $loggedInUser->getPhotoPath(),
                                ],
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE);

                            $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                            /** @var RequestPossibleApprovers $possibleApprover */
                            foreach ($possibleApprovers as $possibleApprover) {
                                $possibleApprover->setHasNotification(true);
                            }

                            $this->entityManager->persist($requestAction);
                            $this->entityManager->flush();

                            $template = 'request/modal/send_message.html.twig';
                        }
                    }
                };

                break;
            case \App\Entity\Request::REQUEST_TYPE_JOIN_COMPANY:

                $companyId = $httpRequest->query->get('company_id');
                $company   = $this->companyRepository->find($companyId);
                /** @var User $createdBy */
                $createdBy = $request->getCreatedBy();
                $template  = 'request/modal/default.html.twig';

                $sendMessageForm = $this->createForm(SendMessageFormType::class, null, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);

                if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
                    $template = 'request/modal/send_message.html.twig';
                }

                $context = [
                    'request' => $request,
                    'company' => $company,
                    'loggedInUser' => $loggedInUser,
                    'sendMessageForm' => $sendMessageForm->createView(),
                ];

                $requestActionHandler = function () use (
                    $request, $createdBy, $company, $requestAction, $action, $loggedInUser, $sendMessageForm,
                    $httpRequest, &
                    $template
                ) {

                    if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {

                        $createdBy->setCompany($company);
                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                ->setStatusLabel('Company invite accepted');

                        $this->requestsMailer->joinCompanyApproved($createdBy, $company);

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_DENY) {

                        if ($createdBy->getCompany()->getId() === $company->getId()) {
                            $createdBy->setCompany(null);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_DENY);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_DENIED)
                                ->setStatusLabel('Company invite denied');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING) {

                        if ($createdBy->getCompany()->getId() === $company->getId()) {
                            $createdBy->setCompany(null);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                ->setStatusLabel('Company invite pending');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {

                        $sendMessageForm->handleRequest($httpRequest);

                        if ($sendMessageForm->isSubmitted() && $sendMessageForm->isValid()) {

                            $formData     = $sendMessageForm->getData();
                            $notification = $request->getNotification();
                            $message      = $formData['message'];

                            $notification['messages'][] = [
                                'body' => $message,
                                'date' => (new \DateTime())->format('n/j/Y g:i A'),
                                'user' => [
                                    'id' => $loggedInUser->getId(),
                                    'full_name' => $loggedInUser->getFullName(),
                                    'photo' => $loggedInUser->getPhotoPath(),
                                ],
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE);

                            $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                            /** @var RequestPossibleApprovers $possibleApprover */
                            foreach ($possibleApprovers as $possibleApprover) {
                                $possibleApprover->setHasNotification(true);
                            }

                            $this->entityManager->persist($requestAction);
                            $this->entityManager->flush();

                            $template = 'request/modal/send_message.html.twig';
                        }
                    }

                };

                break;
            case \App\Entity\Request::REQUEST_TYPE_COMPANY_INVITE:

                $companyId = $httpRequest->query->get('company_id');
                $company   = $this->companyRepository->find($companyId);
                /** @var User $createdBy */
                $createdBy = $request->getCreatedBy();
                $template  = 'request/modal/default.html.twig';

                $sendMessageForm = $this->createForm(SendMessageFormType::class, null, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);

                if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
                    $template = 'request/modal/send_message.html.twig';
                }

                $context = [
                    'request' => $request,
                    'company' => $company,
                    'loggedInUser' => $loggedInUser,
                    'sendMessageForm' => $sendMessageForm->createView(),
                ];

                $requestActionHandler = function () use (
                    $request, $createdBy, $company, $requestAction, $action, $loggedInUser, $sendMessageForm,
                    $httpRequest, &
                    $template
                ) {

                    if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {

                        $loggedInUser->setCompany($company);
                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                ->setStatusLabel('Company invite accepted');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $this->requestsMailer->companyInviteApproved($loggedInUser, $possibleApprover->getPossibleApprover(), $company);
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_DENY) {

                        if ($loggedInUser->getCompany()->getId() === $company->getId()) {
                            $loggedInUser->setCompany(null);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_DENY);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_DENIED)
                                ->setStatusLabel('Company invite denied');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING) {

                        if ($loggedInUser->getCompany()->getId() === $company->getId()) {
                            $loggedInUser->setCompany(null);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                ->setStatusLabel('Company invite pending');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {

                        $sendMessageForm->handleRequest($httpRequest);

                        if ($sendMessageForm->isSubmitted() && $sendMessageForm->isValid()) {

                            $formData     = $sendMessageForm->getData();
                            $notification = $request->getNotification();
                            $message      = $formData['message'];

                            $notification['messages'][] = [
                                'body' => $message,
                                'date' => (new \DateTime())->format('n/j/Y g:i A'),
                                'user' => [
                                    'id' => $loggedInUser->getId(),
                                    'full_name' => $loggedInUser->getFullName(),
                                    'photo' => $loggedInUser->getPhotoPath(),
                                ],
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE);

                            $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                            /** @var RequestPossibleApprovers $possibleApprover */
                            foreach ($possibleApprovers as $possibleApprover) {
                                $possibleApprover->setHasNotification(true);
                            }

                            $this->entityManager->persist($requestAction);
                            $this->entityManager->flush();

                            $template = 'request/modal/send_message.html.twig';
                        }
                    }

                };

                break;
            case \App\Entity\Request::REQUEST_TYPE_TEACH_LESSON_INVITE:

                $lessonId     = $httpRequest->query->get('lesson_id');
                $lesson       = $this->lessonRepository->find($lessonId);
                $data         = null;
                $notification = $request->getNotification();
                $messages     = $notification['messages'] ?? [];

                $setDates = (
                    !empty($notification['suggested_dates']['date_option_one']) &&
                    !empty($notification['suggested_dates']['date_option_two']) &&
                    !empty($notification['suggested_dates']['date_option_three'])
                );

                if ($setDates) {
                    $data = [
                        'dateOptionOne' => DateTime::createFromFormat('m/d/Y g:i A', $notification['suggested_dates']['date_option_one']),
                        'dateOptionTwo' => DateTime::createFromFormat('m/d/Y g:i A', $notification['suggested_dates']['date_option_two']),
                        'dateOptionThree' => DateTime::createFromFormat('m/d/Y g:i A', $notification['suggested_dates']['date_option_three']),
                    ];
                }

                $selectSuggestedDatesForm = $this->createForm(SelectSuggestedDatesFormType::class, null, [
                    'request' => $request,
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_APPROVE,
                ]);

                $suggestNewDatesForm = $this->createForm(SuggestNewDatesFormType::class, $data, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                ]);

                $sendMessageForm = $this->createForm(SendMessageFormType::class, null, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);

                /** @var User $createdBy */
                $createdBy = $request->getCreatedBy();
                $template  = 'request/modal/teach_lesson.html.twig';

                if ($action === RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES) {
                    $template = 'request/modal/suggest_new_dates.html.twig';
                }

                if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {
                    $template = 'request/modal/select_suggested_dates.html.twig';
                }

                if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
                    $template = 'request/modal/send_message.html.twig';
                }

                $context = [
                    'request' => $request,
                    'lesson' => $lesson,
                    'suggestNewDatesForm' => $suggestNewDatesForm->createView(),
                    'selectSuggestedDatesForm' => $selectSuggestedDatesForm->createView(),
                    'sendMessageForm' => $sendMessageForm->createView(),
                    'loggedInUser' => $loggedInUser,
                    'messages' => $messages,
                ];

                $requestActionHandler = function () use (
                    $request, $createdBy, $lesson, $requestAction, $action, $loggedInUser, $selectSuggestedDatesForm,
                    $suggestNewDatesForm, $sendMessageForm, $httpRequest, &$template
                ) {

                    if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {

                        $selectedDate = null;
                        $selectSuggestedDatesForm->handleRequest($httpRequest);

                        if ($selectSuggestedDatesForm->isSubmitted() && $selectSuggestedDatesForm->isValid()) {

                            $notification = $request->getNotification();

                            if ($selectSuggestedDatesForm->get('dateOptionOne')->isClicked()) {
                                $selectedDate = $notification['suggested_dates']['date_option_one'];
                            }

                            if ($selectSuggestedDatesForm->get('dateOptionTwo')->isClicked()) {
                                $selectedDate = $notification['suggested_dates']['date_option_two'];
                            }

                            if ($selectSuggestedDatesForm->get('dateOptionThree')->isClicked()) {
                                $selectedDate = $notification['suggested_dates']['date_option_three'];
                            }

                            $notification['body']['Selected Date'] = [
                                'order' => 5,
                                'value' => $selectedDate ?? '',
                            ];

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                            $request->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                    ->setStatusLabel('Guest instructor invite has been approved')
                                    ->setNotification($notification);
                            $this->entityManager->persist($requestAction);

                            $this->requestsMailer->teachLessonInviteApproved($request->getCreatedBy(), $loggedInUser, $lesson);

                            /** @var RequestPossibleApprovers $possibleApprover */
                            $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser);
                            $possibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);
                            $this->entityManager->persist($possibleApprover);

                            /** @var RequestPossibleApprovers $possibleApprover */
                            foreach ($request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser) as $possibleApprover) {
                                $possibleApprover->setPossibleActions([
                                    RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                ]);
                                $possibleApprover->setHasNotification(true);
                                $this->entityManager->persist($possibleApprover);
                            }

                            $hasProfessional = (
                                !empty($notification['data']['professional_id']) &&
                                $professional = $this->professionalUserRepository->find($notification['data']['professional_id'])
                            );

                            $hasSchool = (
                                !empty($notification['data']['school_id']) &&
                                $school = $this->schoolRepository->find($notification['data']['school_id'])
                            );

                            $hasEducator = (
                                !empty($notification['data']['educator_id']) &&
                                $educator = $this->educatorUserRepository->find($notification['data']['educator_id'])
                            );

                            $shouldCreateExperience = (
                                $hasProfessional &&
                                $hasSchool &&
                                $hasEducator
                            );

                            if ($shouldCreateExperience) {

                                $oldExperience = $this->experienceRepository->findOneBy([
                                    'request' => $request,
                                ]);

                                if ($oldExperience) {
                                    $this->entityManager->remove($oldExperience);
                                }

                                $teachLessonExperience = new TeachLessonExperience();
                                $teachLessonExperience->setStartDateAndTime($selectedDate ? DateTime::createFromFormat('m/d/Y g:i A', $selectedDate) : null);
                                $teachLessonExperience->setEndDateAndTime($selectedDate ? DateTime::createFromFormat('m/d/Y g:i A', $selectedDate)->add(new DateInterval('PT2H')) : null);
                                $teachLessonExperience->setTitle(sprintf("Topic: %s with Guest Instructor %s, in %s Class", $lesson->getTitle(), $professional->getFullName(), $educator->getFullName()));
                                $teachLessonExperience->setBriefDescription(sprintf("%s", $lesson->getShortDescription()));
                                $teachLessonExperience->setRequest($request);
                                $teachLessonExperience->setLesson($lesson);
                                $teachLessonExperience->setSchool($school);
                                $teachLessonExperience->setTeacher($professional);
                                $this->entityManager->persist($teachLessonExperience);

                                $registrationUsers = [$educator, $professional];
                                foreach ($registrationUsers as $registrationUser) {
                                    $registration = new Registration();
                                    $registration->setUser($registrationUser);
                                    $registration->setExperience($teachLessonExperience);
                                    $this->entityManager->persist($registration);
                                }

                            }

                            $this->entityManager->flush();

                            $template = 'request/modal/teach_lesson.html.twig';
                        }
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {

                        $sendMessageForm->handleRequest($httpRequest);

                        if ($sendMessageForm->isSubmitted() && $sendMessageForm->isValid()) {

                            $formData     = $sendMessageForm->getData();
                            $notification = $request->getNotification();
                            $message      = $formData['message'];

                            $notification['messages'][] = [
                                'body' => $message,
                                'date' => (new \DateTime())->format('n/j/Y g:i A'),
                                'user' => [
                                    'id' => $loggedInUser->getId(),
                                    'full_name' => $loggedInUser->getFullName(),
                                    'photo' => $loggedInUser->getPhotoPath(),
                                ],
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE);

                            $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                            /** @var RequestPossibleApprovers $possibleApprover */
                            foreach ($possibleApprovers as $possibleApprover) {
                                $possibleApprover->setHasNotification(true);
                            }

                            $this->entityManager->persist($requestAction);
                            $this->entityManager->flush();

                            $template = 'request/modal/send_message.html.twig';
                        }
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_DENY) {

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_DENY);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_DENIED)
                                ->setStatusLabel('Guest instructor invite has been denied');

                        $this->requestsMailer->teachLessonInviteDenied($request->getCreatedBy(), $loggedInUser, $lesson);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser);
                        $possibleApprover->removePossibleAction([RequestAction::REQUEST_ACTION_NAME_DENY]);
                        $possibleApprover->setPossibleActions([
                            RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                            RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                        ]);

                        $teachLessonExperience = $this->teachLessonExperienceRepository->findOneBy([
                            'request' => $request->getId(),
                        ]);

                        if ($teachLessonExperience) {
                            $this->entityManager->remove($teachLessonExperience);
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();

                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING) {

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                ->setStatusLabel('Guest instructor invite is pending approval');

                        $teachLessonExperience = $this->teachLessonExperienceRepository->findOneBy([
                            'request' => $request->getId(),
                        ]);

                        if ($teachLessonExperience) {
                            $this->entityManager->remove($teachLessonExperience);
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES) {

                        $suggestNewDatesForm->handleRequest($httpRequest);

                        if ($suggestNewDatesForm->isSubmitted() && $suggestNewDatesForm->isValid()) {

                            $formData        = $suggestNewDatesForm->getData();
                            $dateOptionOne   = $formData['dateOptionOne']->format("m/d/Y g:i A");
                            $dateOptionTwo   = $formData['dateOptionTwo']->format("m/d/Y g:i A");
                            $dateOptionThree = $formData['dateOptionThree']->format("m/d/Y g:i A");

                            $notification                                         = $request->getNotification();
                            $notification['suggested_dates']['date_option_one']   = $dateOptionOne;
                            $notification['suggested_dates']['date_option_two']   = $dateOptionTwo;
                            $notification['suggested_dates']['date_option_three'] = $dateOptionThree;
                            $notification['body']['Suggested Dates']              = [
                                'order' => 4,
                                'value' => "{$dateOptionOne} <br> {$dateOptionTwo} <br> {$dateOptionThree}",
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES);

                            $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                    ->setStatusLabel('New suggested dates pending approval');


                            $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser);

                            if ($possibleApprover) {
                                $possibleApprover->setPossibleActions([
                                    RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                ]);
                            }

                            /** @var RequestPossibleApprovers $possibleApprover */
                            foreach ($request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser) as $possibleApprover) {
                                $possibleApprover->setPossibleActions([
                                    RequestAction::REQUEST_ACTION_NAME_APPROVE,
                                    RequestAction::REQUEST_ACTION_NAME_DENY,
                                    RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                                    RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                ]);

                                $possibleApprover->setHasNotification(true);

                                $this->entityManager->persist($possibleApprover);
                            }

                            $this->entityManager->persist($requestAction);
                            $this->entityManager->flush();

                            $template = 'request/modal/teach_lesson.html.twig';
                        }

                    }

                };

                break;

            case \App\Entity\Request::REQUEST_TYPE_JOB_BOARD:

                $template = 'request/modal/job_board.html.twig';

                $sendMessageForm = $this->createForm(SendMessageFormType::class, null, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);

                if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
                    $template = 'request/modal/send_message.html.twig';
                }

                $context = [
                    'request' => $request,
                    'loggedInUser' => $loggedInUser,
                    'sendMessageForm' => $sendMessageForm->createView(),
                ];

                $requestActionHandler = function () use (
                    $request, $requestAction, $action, $loggedInUser, $sendMessageForm, $httpRequest, &
                    $template
                ) {

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_ACTIVE) {

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_ACTIVE);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_ACTIVE)
                                ->setStatusLabel('Active Job Posting');

                        foreach ($request->getRequests() as $childRequest) {
                            $childRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_ACTIVE)
                                         ->setStatusLabel('Active Job Posting');
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_INACTIVE) {

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_INACTIVE);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_INACTIVE)
                                ->setStatusLabel('Inactive Job Posting');

                        foreach ($request->getRequests() as $childRequest) {
                            $childRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_INACTIVE)
                                         ->setStatusLabel('Inactive Job Posting');
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {

                        $sendMessageForm->handleRequest($httpRequest);

                        if ($sendMessageForm->isSubmitted() && $sendMessageForm->isValid()) {

                            $formData     = $sendMessageForm->getData();
                            $notification = $request->getNotification();
                            $message      = $formData['message'];

                            $notification['messages'][] = [
                                'body' => $message,
                                'date' => (new \DateTime())->format('n/j/Y g:i A'),
                                'user' => [
                                    'id' => $loggedInUser->getId(),
                                    'full_name' => $loggedInUser->getFullName(),
                                    'photo' => $loggedInUser->getPhotoPath(),
                                ],
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE);
                            $this->entityManager->persist($requestAction);

                            $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                            if (empty($possibleApprovers)) {
                                $possibleApprover = new RequestPossibleApprovers();
                                $possibleApprover->setPossibleApprover($request->getCreatedBy());
                                $possibleApprover->setRequest($request);
                                $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE]);
                                $possibleApprover->setNotificationTitle("<strong>{$loggedInUser->getFullName()}</strong> responded to your job board request - \"{$request->getSummary()}\"");
                                $this->entityManager->persist($possibleApprover);
                                $possibleApprovers = [$possibleApprover];
                            }

                            foreach ($possibleApprovers as $possibleApprover) {
                                $possibleApprover->setHasNotification(true);
                            }

                            $this->entityManager->flush();

                            $template = 'request/modal/send_message.html.twig';
                        }
                    }
                };

                break;

            case \App\Entity\Request::REQUEST_TYPE_NEW_REGISTRATION:

                $experienceId   = $httpRequest->query->get('experience_id');
                $experience     = $this->experienceRepository->find($experienceId);
                $userToRegister = $request->getCreatedBy();

                if (!empty($request->getNotification()['data']['user_to_register'])) {
                    $userToRegister = $request->getNotification()['data']['user_to_register'];
                    $userToRegister = $this->userRepository->find($userToRegister);
                }

                $template = 'request/modal/new_registration.html.twig';

                $sendMessageForm = $this->createForm(SendMessageFormType::class, null, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);

                if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
                    $template = 'request/modal/send_message.html.twig';
                }

                if ($action === RequestAction::REQUEST_ACTION_NAME_VIEW_REGISTRATION_LIST) {
                    $template = 'request/modal/registration_list.html.twig';
                }

                $context = [
                    'experience' => $experience,
                    'request' => $request,
                    'loggedInUser' => $loggedInUser,
                    'sendMessageForm' => $sendMessageForm->createView(),
                ];

                $requestActionHandler = function () use (
                    $request, $requestAction, $action, $loggedInUser, $sendMessageForm, $httpRequest, $experience,
                    $userToRegister, &
                    $template
                ) {

                    if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {

                        $registration = $this->registrationRepository->findOneBy([
                            'user' => $userToRegister,
                            'experience' => $experience,
                        ]);

                        if (!$registration) {
                            $registration = new Registration();
                            $registration->setUser($userToRegister);
                            $registration->setExperience($experience);
                            $this->entityManager->persist($registration);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                ->setStatusLabel('Registration has been approved');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {

                            $this->requestsMailer->userRegisterationApproved($possibleApprover->getPossibleApprover(), $experience);

                            $possibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                RequestAction::REQUEST_ACTION_NAME_UNREGISTER,
                            ]);

                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_DENY) {

                        $registration = $this->registrationRepository->findOneBy([
                            'user' => $userToRegister,
                            'experience' => $experience,
                        ]);

                        if ($registration) {
                            $this->entityManager->remove($registration);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_DENY);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_DENIED)
                                ->setStatusLabel('Registration has been denied');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_UNREGISTER) {

                        $registration = $this->registrationRepository->findOneBy([
                            'user' => $userToRegister,
                            'experience' => $experience,
                        ]);

                        if ($registration) {
                            $this->entityManager->remove($registration);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_UNREGISTER);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_UNREGISTERED)
                                ->setStatusLabel('Unregistered');

                        if ($createdByApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser)) {
                            $createdByApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                RequestAction::REQUEST_ACTION_NAME_REGISTER,
                            ]);
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {

                            $possibleApprover->setPossibleActions(
                                [
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                    RequestAction::REQUEST_ACTION_NAME_VIEW_REGISTRATION_LIST,
                                ]
                            );

                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_REGISTER) {

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                ->setStatusLabel('Registration Pending Approval');

                        if ($createdByApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser)) {
                            $createdByApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                RequestAction::REQUEST_ACTION_NAME_UNREGISTER,
                            ]);
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {

                            $possibleApprover->setPossibleActions(
                                [
                                    RequestAction::REQUEST_ACTION_NAME_APPROVE,
                                    RequestAction::REQUEST_ACTION_NAME_DENY,
                                    RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                    RequestAction::REQUEST_ACTION_NAME_VIEW_REGISTRATION_LIST,
                                ]
                            );

                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING) {

                        $registration = $this->registrationRepository->findOneBy([
                            'user' => $userToRegister,
                            'experience' => $experience,
                        ]);

                        if ($registration) {
                            $this->entityManager->remove($registration);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                ->setStatusLabel('Registration Pending Approval');

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        /** @var RequestPossibleApprovers $possibleApprover */
                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }


                    if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {

                        $sendMessageForm->handleRequest($httpRequest);

                        if ($sendMessageForm->isSubmitted() && $sendMessageForm->isValid()) {

                            $formData     = $sendMessageForm->getData();
                            $notification = $request->getNotification();
                            $message      = $formData['message'];

                            $notification['messages'][] = [
                                'body' => $message,
                                'date' => (new \DateTime())->format('n/j/Y g:i A'),
                                'user' => [
                                    'id' => $loggedInUser->getId(),
                                    'full_name' => $loggedInUser->getFullName(),
                                    'photo' => $loggedInUser->getPhotoPath(),
                                ],
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE);
                            $this->entityManager->persist($requestAction);

                            $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                            if (empty($possibleApprovers)) {
                                $possibleApprover = new RequestPossibleApprovers();
                                $possibleApprover->setPossibleApprover($request->getCreatedBy());
                                $possibleApprover->setRequest($request);
                                $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE]);
                                $possibleApprover->setNotificationTitle("<strong>{$loggedInUser->getFullName()}</strong> responded to your job board request - \"{$request->getSummary()}\"");
                                $this->entityManager->persist($possibleApprover);
                                $possibleApprovers = [$possibleApprover];
                            }

                            foreach ($possibleApprovers as $possibleApprover) {
                                $possibleApprover->setHasNotification(true);
                            }

                            $this->entityManager->flush();

                            $template = 'request/modal/send_message.html.twig';
                        }
                    }
                };

                break;

            case \App\Entity\Request::REQUEST_TYPE_ONE_ON_ONE_MEETING:

                $data           = null;
                $notification   = $request->getNotification();
                $professionalId = $httpRequest->query->get('professional_id');
                $professional   = $this->professionalUserRepository->find($professionalId);

                $studentId = $httpRequest->query->get('student_id');
                $student   = $this->studentUserRepository->find($studentId);

                /** @var User $createdBy */
                $createdBy = $request->getCreatedBy();

                $template = 'request/modal/one_on_one_meeting.html.twig';

                $setDates = (
                    !empty($notification['suggested_dates']['date_option_one']) &&
                    !empty($notification['suggested_dates']['date_option_two']) &&
                    !empty($notification['suggested_dates']['date_option_three'])
                );

                if ($setDates) {
                    $data = [
                        'dateOptionOne' => DateTime::createFromFormat('m/d/Y g:i A', $notification['suggested_dates']['date_option_one']),
                        'dateOptionTwo' => DateTime::createFromFormat('m/d/Y g:i A', $notification['suggested_dates']['date_option_two']),
                        'dateOptionThree' => DateTime::createFromFormat('m/d/Y g:i A', $notification['suggested_dates']['date_option_three']),
                    ];
                }

                $selectSuggestedDatesForm = $this->createForm(SelectSuggestedDatesFormType::class, null, [
                    'request' => $request,
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_APPROVE,
                ]);

                $suggestNewDatesForm = $this->createForm(SuggestNewDatesFormType::class, $data, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                ]);

                $sendMessageForm = $this->createForm(SendMessageFormType::class, null, [
                    'method' => 'post',
                    'action' => $request->getActionUrl() . '&action=' . RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);

                if ($action === RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES) {
                    $template = 'request/modal/suggest_new_dates.html.twig';
                }

                if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {
                    $template = 'request/modal/select_suggested_dates.html.twig';
                }

                if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
                    $template = 'request/modal/send_message.html.twig';
                }

                $context = [
                    'request' => $request,
                    'suggestNewDatesForm' => $suggestNewDatesForm->createView(),
                    'selectSuggestedDatesForm' => $selectSuggestedDatesForm->createView(),
                    'sendMessageForm' => $sendMessageForm->createView(),
                    'loggedInUser' => $loggedInUser,
                ];

                $requestActionHandler = function () use (
                    $request, $createdBy, $professional, $student, $requestAction, $action, $loggedInUser,
                    $selectSuggestedDatesForm, $suggestNewDatesForm, $sendMessageForm, $httpRequest, &$template
                ) {

                    if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {

                        /** @var  RequestPossibleApprovers $loggedInUserPossibleApprover */
                        $loggedInUserPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser);
                        $loggedInUserPossibleApprover->setNotificationDate(new \DateTime());
                        $notification = $request->getNotification();


                        if ($loggedInUser instanceof EducatorUser) {

                            /** @var  RequestPossibleApprovers $professionalPossibleApprover */
                            $professionalPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($professional);

                            if (!$professionalPossibleApprover) {
                                // assign to professional for approval and date selection
                                $notification['user_photos'][] = [
                                    'order' => count($notification['user_photos']) + 1,
                                    'path' => $professional->getPhotoPath(),
                                ];

                                $request->setNotification($notification);

                                $professionalPossibleApprover = new RequestPossibleApprovers();
                                $professionalPossibleApprover->setPossibleApprover($professional);
                                $professionalPossibleApprover->setRequest($request);
                                $professionalPossibleApprover->setNotificationTitle("<strong>{$student->getFullName()}</strong> has requested to meet you");
                                $professionalPossibleApprover->setHasNotification(true);
                                $professionalPossibleApprover->setPossibleActions([
                                    RequestAction::REQUEST_ACTION_NAME_DENY,
                                    RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                                    RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                ]);
                                $this->entityManager->persist($professionalPossibleApprover);
                                $this->requestsMailer->oneOnOneMeetingApproval($professionalPossibleApprover->getPossibleApprover());

                                /** @var  RequestPossibleApprovers $studentPossibleApprover */
                                $studentPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($student);

                                $studentPossibleApprover->setHasNotification(true);
                                $studentPossibleApprover->setPossibleActions([
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                ]);
                            }

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                            $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                    ->setStatusLabel('Meeting Pending Professional Approval');
                        } else {

                            $selectedDate = null;
                            $reasonToMeet = '';
                            $selectSuggestedDatesForm->handleRequest($httpRequest);

                            if ($selectSuggestedDatesForm->isSubmitted() && $selectSuggestedDatesForm->isValid()) {

                                $notification = $request->getNotification();

                                if ($selectSuggestedDatesForm->get('dateOptionOne')->isClicked()) {
                                    $selectedDate = $notification['suggested_dates']['date_option_one'];
                                }

                                if ($selectSuggestedDatesForm->get('dateOptionTwo')->isClicked()) {
                                    $selectedDate = $notification['suggested_dates']['date_option_two'];
                                }

                                if ($selectSuggestedDatesForm->get('dateOptionThree')->isClicked()) {
                                    $selectedDate = $notification['suggested_dates']['date_option_three'];
                                }

                                if (!empty($notification['body']['Reason to Meet'])) {
                                    $reasonToMeet = $notification['body']['Reason to Meet']['value'];
                                }

                                $notification['body']['Selected Meeting Date'] = [
                                    'order' => 5,
                                    'value' => $selectedDate ?? '',
                                ];

                                $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                                $request->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                        ->setStatusLabel('Meeting has been approved')
                                        ->setNotification($notification);
                                $this->entityManager->persist($requestAction);

                                /** @var  RequestPossibleApprovers $studentPossibleApprover */
                                $studentPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($student);
                                $studentPossibleApprover->setPossibleActions([
                                    RequestAction::REQUEST_ACTION_NAME_DENY,
                                    RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                ]);

                                /** @var  RequestPossibleApprovers $professionalPossibleApprover */
                                $professionalPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($professional);
                                $professionalPossibleApprover->setPossibleActions([
                                    RequestAction::REQUEST_ACTION_NAME_DENY,
                                    RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                                ]);

                                /** @var RequestPossibleApprovers $possibleApprover */
                                foreach ($request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser) as $possibleApprover) {
                                    $possibleApprover->setNotificationDate(new DateTime());
                                }

                                $hasProfessional = (
                                    !empty($notification['data']['professional_id']) &&
                                    $professional = $this->professionalUserRepository->find($notification['data']['professional_id'])
                                );

                                $hasStudent = (
                                    !empty($notification['data']['student_id']) &&
                                    $student = $this->studentUserRepository->find($notification['data']['student_id'])
                                );

                                $shouldCreateExperience = (
                                    $hasProfessional &&
                                    $hasStudent
                                );

                                if ($shouldCreateExperience) {

                                    $experience = $this->experienceRepository->findOneBy([
                                        'request' => $request,
                                    ]);

                                    if (!$experience) {
                                        $experience = new StudentToMeetProfessionalExperience();
                                        $experience->setTitle(sprintf("One on One Meeting - %s", $reasonToMeet));
                                        $experience->setBriefDescription(sprintf("One on One Meeting With %s and %s - %s", $professional->getFullName(), $student->getFullName(), $reasonToMeet));
                                        $experience->setRequest($request);

                                        $registrationUsers = [$student, $professional];
                                        foreach ($registrationUsers as $registrationUser) {
                                            $registration = new Registration();
                                            $registration->setUser($registrationUser);
                                            $registration->setExperience($experience);
                                            $this->entityManager->persist($registration);
                                        }
                                    }

                                    $experience->setStartDateAndTime($selectedDate ? DateTime::createFromFormat('m/d/Y g:i A', $selectedDate) : null);
                                    $experience->setEndDateAndTime($selectedDate ? DateTime::createFromFormat('m/d/Y g:i A', $selectedDate)->add(new DateInterval('PT2H')) : null);
                                    $this->entityManager->persist($experience);
                                }

                                $this->entityManager->flush();

                                $template = 'request/modal/one_on_one_meeting.html.twig';
                            }


                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                            $request->setStatus(\App\Entity\Request::REQUEST_STATUS_APPROVED)
                                    ->setStatusLabel('Meeting Has Been Approved');
                        }

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();

                        $template = 'request/modal/one_on_one_meeting.html.twig';
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_DENY) {

                        if ($loggedInUser instanceof EducatorUser) {
                            /** @var  RequestPossibleApprovers $professionalPossibleApprover */
                            $professionalPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($professional);

                            if ($professionalPossibleApprover) {
                                $professionalPossibleApprover->setPossibleActions([]);
                            }

                            $notification = $request->getNotification();

                            $notification['body']['Selected Meeting Date'] = [
                                'order' => 5,
                                'value' => "To be determined",
                            ];

                            $notification['body']['Suggested Meeting Dates'] = [
                                'order' => 6,
                                'value' => "To be determined",
                            ];

                            $request->setNotification($notification);

                            /** @var  RequestPossibleApprovers $studentPossibleApprover */
                            $studentPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($student);
                            $studentPossibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);
                        } else {

                            if ($loggedInUser instanceof ProfessionalUser) {
                                /** @var  RequestPossibleApprovers $possibleApprover */
                                $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($student);
                            }

                            if ($loggedInUser instanceof StudentUser) {
                                /** @var  RequestPossibleApprovers $possibleApprover */
                                $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($professional);
                            }

                            $possibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);
                        }

                        $experience = $this->experienceRepository->findOneBy([
                            'request' => $request,
                        ]);

                        if ($experience) {
                            $this->entityManager->remove($experience);
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_DENY);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_DENIED)
                                ->setStatusLabel('Meeting Has Been Denied');

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING) {

                        if ($loggedInUser instanceof EducatorUser) {
                            /** @var  RequestPossibleApprovers $professionalPossibleApprover */
                            $professionalPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($professional);

                            if ($professionalPossibleApprover) {
                                $professionalPossibleApprover->setPossibleActions([]);
                                $professionalPossibleApprover->setHasNotification(true);
                            }

                            $experience = $this->experienceRepository->findOneBy([
                                'request' => $request,
                            ]);

                            if ($experience) {
                                $this->entityManager->remove($experience);
                            }

                            $notification = $request->getNotification();

                            $notification['body']['Selected Meeting Date'] = [
                                'order' => 5,
                                'value' => "To be determined",
                            ];

                            $notification['body']['Suggested Meeting Dates'] = [
                                'order' => 6,
                                'value' => "To be determined",
                            ];

                            $request->setNotification($notification);

                            /** @var  RequestPossibleApprovers $studentPossibleApprover */
                            $studentPossibleApprover = $request->getAssociatedRequestPossibleApproverForUser($student);
                            $studentPossibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);
                        } else {

                            if ($loggedInUser instanceof ProfessionalUser) {
                                /** @var  RequestPossibleApprovers $possibleApprover */
                                $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($student);
                            }

                            if ($loggedInUser instanceof StudentUser) {
                                /** @var  RequestPossibleApprovers $possibleApprover */
                                $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($professional);
                            }

                            $possibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);
                        }

                        $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                        foreach ($possibleApprovers as $possibleApprover) {
                            $possibleApprover->setHasNotification(true);
                        }

                        $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING);
                        $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                ->setStatusLabel('Meeting Pending Approval');

                        $this->entityManager->persist($requestAction);
                        $this->entityManager->flush();
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {

                        $sendMessageForm->handleRequest($httpRequest);

                        if ($sendMessageForm->isSubmitted() && $sendMessageForm->isValid()) {

                            $formData     = $sendMessageForm->getData();
                            $notification = $request->getNotification();
                            $message      = $formData['message'];

                            $notification['messages'][] = [
                                'body' => $message,
                                'date' => (new \DateTime())->format('n/j/Y g:i A'),
                                'user' => [
                                    'id' => $loggedInUser->getId(),
                                    'full_name' => $loggedInUser->getFullName(),
                                    'photo' => $loggedInUser->getPhotoPath(),
                                ],
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE);
                            $this->entityManager->persist($requestAction);

                            $possibleApprovers = $request->getAssociatedRequestPossibleApproversNotEqualToUser($loggedInUser);

                            if (empty($possibleApprovers)) {
                                $possibleApprover = new RequestPossibleApprovers();
                                $possibleApprover->setPossibleApprover($request->getCreatedBy());
                                $possibleApprover->setRequest($request);
                                $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE]);
                                $possibleApprover->setNotificationTitle("<strong>{$loggedInUser->getFullName()}</strong> responded to your job board request - \"{$request->getSummary()}\"");
                                $this->entityManager->persist($possibleApprover);
                                $possibleApprovers = [$possibleApprover];
                            }

                            foreach ($possibleApprovers as $possibleApprover) {
                                $possibleApprover->setHasNotification(true);
                            }

                            $this->entityManager->flush();

                            $template = 'request/modal/send_message.html.twig';
                        }
                    }

                    if ($action === RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES) {

                        $suggestNewDatesForm->handleRequest($httpRequest);

                        if ($suggestNewDatesForm->isSubmitted() && $suggestNewDatesForm->isValid()) {

                            $formData        = $suggestNewDatesForm->getData();
                            $dateOptionOne   = $formData['dateOptionOne']->format("m/d/Y g:i A");
                            $dateOptionTwo   = $formData['dateOptionTwo']->format("m/d/Y g:i A");
                            $dateOptionThree = $formData['dateOptionThree']->format("m/d/Y g:i A");

                            $notification                                         = $request->getNotification();
                            $notification['suggested_dates']['date_option_one']   = $dateOptionOne;
                            $notification['suggested_dates']['date_option_two']   = $dateOptionTwo;
                            $notification['suggested_dates']['date_option_three'] = $dateOptionThree;
                            $notification['body']['Suggested Meeting Dates']      = [
                                'order' => 4,
                                'value' => "{$dateOptionOne} <br> {$dateOptionTwo} <br> {$dateOptionThree}",
                            ];

                            $request->setNotification($notification);

                            $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES);

                            $request->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING)
                                    ->setStatusLabel('Suggested meeting dates pending approval');

                            /** @var RequestPossibleApprovers $possibleApprover */
                            $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($loggedInUser);
                            $possibleApprover->setNotificationDate(new \DateTime());
                            $possibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_DENY,
                                RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);

                            if ($loggedInUser instanceof ProfessionalUser) {
                                /** @var  RequestPossibleApprovers $possibleApprover */
                                $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($student);
                            }

                            if ($loggedInUser instanceof StudentUser) {
                                /** @var  RequestPossibleApprovers $possibleApprover */
                                $possibleApprover = $request->getAssociatedRequestPossibleApproverForUser($professional);
                            }

                            $possibleApprover->setHasNotification(true);
                            $possibleApprover->setPossibleActions([
                                RequestAction::REQUEST_ACTION_NAME_APPROVE,
                                RequestAction::REQUEST_ACTION_NAME_DENY,
                                RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                                RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES,
                                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                            ]);

                            $this->entityManager->persist($requestAction);
                            $this->entityManager->flush();

                            $template = 'request/modal/one_on_one_meeting.html.twig';
                        }
                    }
                };

                break;

            default:
                $template = 'request/modal/default.html.twig';
                $context  = [
                    'request' => $request,
                ];
                break;
        }

        if ($httpRequest->getMethod() === 'POST') {
            $requestActionHandler();
            $emailHandler();

            //return $this->redirectToRoute('requests');
        }

        return new JsonResponse(
            [
                'formMarkup' => $this->renderView($template, $context),
            ], Response::HTTP_OK
        );
    }

    /**
     * @IsGranted("ROLE_STUDENT_USER")
     * @Route("/requests/student-to-meet-professional", name="student_request_to_meet_professional", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function studentRequestToMeetProfessionalAction(Request $request)
    {
        /** @var User $user */
        $user           = $this->getUser();
        $studentId      = $request->request->get('studentId');
        $student        = $this->studentUserRepository->find($studentId);
        $professionalId = $request->request->get('professionalId');
        $professional   = $this->professionalUserRepository->find($professionalId);
        $reasonToMeet   = $request->request->get('reasonToMeet');
        $reasonToMeet   = $this->rolesWillingToFulfillRepository->findOneBy([
            'eventName' => $reasonToMeet,
        ]);

        $notAuthorized = (
            !$student ||
            !$professional ||
            !$student->isCommunicationEnabled() ||
            !$student->getEducatorUsers()->count()
        );

        if ($notAuthorized) {
            $this->addFlash('error', 'You are not able to perform that action at this time.');

            return $this->redirectToRoute('profile_index', ['id' => $professional->getId()]);
        }

        $oneOnOneMeetingRequest = new \App\Entity\Request();
        $oneOnOneMeetingRequest->setRequestType(\App\Entity\Request::REQUEST_TYPE_ONE_ON_ONE_MEETING);
        $oneOnOneMeetingRequest->setCreatedBy($user);
        $oneOnOneMeetingRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING);
        $oneOnOneMeetingRequest->setStatusLabel('Meeting Pending Approval');

        $notification = [
            'title' => "<strong>{$user->getFullName()}</strong> has requested to meet \"{$professional->getFullName()}\"",
            'data' => [
                'professional_id' => $professional->getId(),
                'student_id' => $user->getId(),
            ],
            'user_photo' => $user->getPhotoPath(),
            'user_photos' => [
                [
                    'order' => 1,
                    'path' => $user->getPhotoPath(),
                ],
            ],
            'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
            'suggested_dates' => [],
            'messages' => [],
            'body' => [
                'Request Type' => [
                    'order' => 1,
                    'value' => 'One on One Meeting',
                ],
                'Initiated By' => [
                    'order' => 2,
                    'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $user->getId()])}'>{$user->getFullName()}</a>",
                ],
                'Sent To' => [
                    'order' => 3,
                    'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $professional->getId()])}'>{$professional->getFullName()}</a>",
                ],
                'Reason to Meet' => [
                    'order' => 4,
                    'value' => $reasonToMeet->getName(),
                ],
                'Selected Meeting Date' => [
                    'order' => 5,
                    'value' => "To be determined",
                ],
                'Suggested Meeting Dates' => [
                    'order' => 6,
                    'value' => "To be determined",
                ],
                'Created On' => [
                    'order' => 7,
                    'value' => (new \DateTime())->format("m/d/Y h:i A"),
                ],
            ],
        ];

        $oneOnOneMeetingRequest->setNotification($notification);

        $this->entityManager->persist($oneOnOneMeetingRequest);
        $this->entityManager->flush();

        $createdByApprover = new RequestPossibleApprovers();
        $createdByApprover->setPossibleApprover($user);
        $createdByApprover->setRequest($oneOnOneMeetingRequest);
        $createdByApprover->setNotificationDate(new \DateTime());
        $createdByApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE]);
        $createdByApprover->setNotificationTitle("<strong>You</strong> have requested to meet \"{$professional->getFullName()}\"");

        if ($student->isTeacherApprovalRequired()) {

            $oneOnOneMeetingRequest->setStatusLabel('Meeting Pending Teacher Approval');

            $userPhotoOrder = 2;
            foreach ($student->getEducatorUsers() as $educatorUser) {

                $notification['user_photos'][] = [
                    'order' => $userPhotoOrder,
                    'path' => $educatorUser->getPhotoPath(),
                ];

                $possibleApprover = new RequestPossibleApprovers();
                $possibleApprover->setPossibleApprover($educatorUser);
                $possibleApprover->setRequest($oneOnOneMeetingRequest);
                $possibleApprover->setHasNotification(true);
                $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_APPROVE,
                                                       RequestAction::REQUEST_ACTION_NAME_DENY,
                                                       RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                                                       RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);
                $possibleApprover->setNotificationTitle("<strong>{$user->getFullName()}</strong> has requested to meet \"{$professional->getFullName()}\"");
                $this->entityManager->persist($possibleApprover);

                $this->requestsMailer->oneOnOneMeetingApproval($possibleApprover->getPossibleApprover());

                $userPhotoOrder++;
            }
        } else {

            $notification['user_photos'][] = [
                'order' => 2,
                'path' => $professional->getPhotoPath(),
            ];

            $possibleApprover = new RequestPossibleApprovers();
            $possibleApprover->setPossibleApprover($professional);
            $possibleApprover->setRequest($oneOnOneMeetingRequest);
            $possibleApprover->setHasNotification(true);
            $possibleApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_APPROVE,
                                                   RequestAction::REQUEST_ACTION_NAME_DENY,
                                                   RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                                                   RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES,
                                                   RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
            ]);
            $possibleApprover->setNotificationTitle("<strong>{$user->getFullName()}</strong> has requested to meet you");
            $this->entityManager->persist($possibleApprover);

            $this->requestsMailer->oneOnOneMeetingApproval($possibleApprover->getPossibleApprover());
        }

        $oneOnOneMeetingRequest->setNotification($notification);

        $this->entityManager->persist($createdByApprover);

        $requestActionUrl = $this->generateUrl('request_action', [
            'request_id' => $oneOnOneMeetingRequest->getId(),
            'professional_id' => $professional->getId(),
            'student_id' => $student->getId(),
        ]);

        $oneOnOneMeetingRequest->setActionUrl($requestActionUrl);

        $this->entityManager->flush();
        $this->entityManager->refresh($oneOnOneMeetingRequest);


        $this->addFlash('success', 'Request to meet successfully sent.');

        return $this->redirectToRoute('profile_index', ['id' => $professional->getId()]);
    }

    /**
     * @Route("/requests/{id}/user_has_seen_request", name="user_has_seen_request", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Twig\Error\SyntaxError
     */
    public function toggleHasUserSeenRequest(\App\Entity\Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        if ($user->isStudent()) {
            $request->setStudentHasSeen(true);
        }
        if ($user->isEducator()) {
            $request->setEducatorHasSeen(true);
        }
        if ($user->isProfessional()) {
            $request->setProfessionalHasSeen(true);
        }
        if ($user->isSchoolAdministrator()) {
            $request->setSchoolAdminHasSeen(true);
        }

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        return new JsonResponse(
            Response::HTTP_OK
        );
    }

    /**
     * @Security("is_granted('ROLE_EDUCATOR_USER')")
     *
     * @Route("/requests/new", name="new_request", options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newRequest(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $jobBoardRequest = new RequestEntity();
        $jobBoardRequest->setRequestType(RequestEntity::REQUEST_TYPE_JOB_BOARD);

        $form = $this->createForm(CreateRequestFormType::class, $jobBoardRequest, [
            'skip_validation' => $request->request->get('skip_validation', false),
        ]);

        $form->handleRequest($request);

        if ($form->get('delete')->isClicked()) {
            $this->addFlash('success', 'Your request has been deleted.');

            return $this->redirectToRoute('new_request');
        }

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var RequestEntity $jobBoardRequest */
            $jobBoardRequest = $form->getData();
            $jobBoardRequest->setCreatedBy($user);
            $this->entityManager->persist($jobBoardRequest);
            $this->entityManager->flush();

            $requestActionUrl = $this->generateUrl('request_action', [
                'request_id' => $jobBoardRequest->getId(),
            ]);

            $jobBoardRequest->setActionUrl($requestActionUrl);

            $jobBoardRequest->setNotification([
                'title' => "<strong>{$user->getFullName()}</strong> posted a new job board request - \"{$jobBoardRequest->getSummary()}\"",
                'data' => [
                    'educator_id' => $user->getId(),
                ],
                'user_photo' => $user->getPhotoPath(),
                'user_photos' => [],
                'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
                'messages' => [],
                'body' => [
                    'Request Type' => [
                        'order' => 1,
                        'value' => "<a target='_blank' href='{$this->generateUrl('view_request', ['id' => $jobBoardRequest->getId()])}'>Job Board Request</a>",
                    ],
                    'Initiated By' => [
                        'order' => 2,
                        'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $user->getId()])}'>{$user->getFullName()}</a>",
                    ],
                    'Summary' => [
                        'order' => 3,
                        'value' => "<a target='_blank' href='{$this->generateUrl('view_request', ['id' => $jobBoardRequest->getId()])}'>{$jobBoardRequest->getSummary()}</a>",
                    ],
                    'Description' => [
                        'order' => 4,
                        'value' => $jobBoardRequest->getDescription(),
                    ],
                    'Volunteers Needed' => [
                        'order' => 5,
                        'value' => implode(", ", array_map(function (RolesWillingToFulfill $rolesWillingToFulfill) {
                            return $rolesWillingToFulfill->getName();
                        }, $jobBoardRequest->getVolunteerRoles()->toArray())),
                    ],
                    'Volunteer Career Sector(s)' => [
                        'order' => 6,
                        'value' => implode(", ", array_map(function (Industry $industry) {
                            return $industry->getName();
                        }, $jobBoardRequest->getPrimaryIndustries()->toArray())),
                    ],
                    'Created On' => [
                        'order' => 7,
                        'value' => (new \DateTime())->format("m/d/Y h:i A"),
                    ],
                ],
            ]);

            $createdByApprover = new RequestPossibleApprovers();
            $createdByApprover->setPossibleApprover($user);
            $createdByApprover->setRequest($jobBoardRequest);
            $createdByApprover->setPossibleActions([RequestAction::REQUEST_ACTION_NAME_MARK_AS_ACTIVE,
                                                    RequestAction::REQUEST_ACTION_NAME_MARK_AS_INACTIVE,
            ]);
            $createdByApprover->setNotificationTitle("<strong>You</strong> have posted a new job board request - \"{$jobBoardRequest->getSummary()}\"");
            $this->entityManager->persist($createdByApprover);

            if ($form->get('postAndReview')->isClicked()) {

                $jobBoardRequest->setPublished(true);
                $jobBoardRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_ACTIVE);
                $jobBoardRequest->setStatusLabel('Active job posting');
                $jobBoardRequest->setRequestActionAt(new \DateTime());
                $createdByApprover->setNotificationDate(new DateTime());

                $this->entityManager->flush();

                // dispatch message
                $status = $this->dispatchMessage(new CreateJobBoardRequest($jobBoardRequest->getId()));

                return $this->redirectToRoute('view_request', ['id' => $jobBoardRequest->getId()]);
            }

            if ($form->get('saveAndPreview')->isClicked()) {
                $jobBoardRequest->setPublished(false);
                $jobBoardRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_INACTIVE);
                $jobBoardRequest->setStatusLabel('Inactive job posting');
                $this->entityManager->flush();

                return $this->redirectToRoute('view_request', ['id' => $jobBoardRequest->getId()]);
            }
        }

        return $this->render('request/new.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_EDUCATOR_USER')")
     *
     * @Route("/requests/{id}/edit", name="edit_request", options = { "expose" = true })
     * @param RequestEntity $requestEntity
     * @param Request       $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function editRequest(RequestEntity $requestEntity, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $accessDenied = (
            $requestEntity->getCreatedBy() &&
            $requestEntity->getCreatedBy()->getId() !== $user->getId()
        );

        if ($accessDenied) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(EditRequestFormType::class, $requestEntity, [
            'skip_validation' => $request->request->get('skip_validation', false),
            'requestEntity' => $requestEntity,
        ]);

        $form->handleRequest($request);

        if ($form->get('delete')->isClicked()) {
            $this->entityManager->remove($requestEntity);
            $this->entityManager->flush();
            $this->addFlash('success', 'Your request has been deleted.');

            return $this->redirectToRoute('new_request');
        }

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var RequestEntity $jobBoardRequest */
            $jobBoardRequest = $form->getData();
            $mesages         = $jobBoardRequest->getNotification()['messages'];
            $createdOn       = $jobBoardRequest->getNotification()['body']['Created On'];

            $jobBoardRequest->setNotification([
                'title' => "<strong>{$user->getFullName()}</strong> posted a new job board request - \"{$jobBoardRequest->getSummary()}\"",
                'data' => [
                    'educator_id' => $user->getId(),
                ],
                'user_photo' => $user->getPhotoPath(),
                'user_photos' => [],
                'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
                'messages' => $mesages,
                'body' => [
                    'Request Type' => [
                        'order' => 1,
                        'value' => "<a target='_blank' href='{$this->generateUrl('view_request', ['id' => $jobBoardRequest->getId()])}'>Job Board Request</a>",
                    ],
                    'Initiated By' => [
                        'order' => 2,
                        'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $user->getId()])}'>{$user->getFullName()}</a>",
                    ],
                    'Summary' => [
                        'order' => 3,
                        'value' => "<a target='_blank' href='{$this->generateUrl('view_request', ['id' => $jobBoardRequest->getId()])}'>{$jobBoardRequest->getSummary()}</a>",
                    ],
                    'Description' => [
                        'order' => 4,
                        'value' => $jobBoardRequest->getDescription(),
                    ],
                    'Volunteers Needed' => [
                        'order' => 5,
                        'value' => implode(", ", array_map(function (RolesWillingToFulfill $rolesWillingToFulfill) {
                            return $rolesWillingToFulfill->getName();
                        }, $jobBoardRequest->getVolunteerRoles()->toArray())),
                    ],
                    'Volunteer Career Sector(s)' => [
                        'order' => 6,
                        'value' => implode(", ", array_map(function (Industry $industry) {
                            return $industry->getName();
                        }, $jobBoardRequest->getPrimaryIndustries()->toArray())),
                    ],
                    'Created On' => $createdOn,
                ],
            ]);


            if ($form->get('postAndReview')->isClicked()) {

                $jobBoardRequest->setPublished(true);
                $jobBoardRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_ACTIVE);
                $jobBoardRequest->setStatusLabel('Active job posting');
                $jobBoardRequest->setRequestActionAt(new \DateTime());

                /** @var RequestPossibleApprovers $createdByApprover */
                if ($createdByApprover = $jobBoardRequest->getAssociatedRequestPossibleApproverForUser($user)) {
                    $createdByApprover->setNotificationTitle("<strong>You</strong> have posted a new job board request - \"{$jobBoardRequest->getSummary()}\"");
                    $createdByApprover->setNotificationDate(new DateTime());
                }

                $this->entityManager->flush();

                $status = $this->dispatchMessage(new CreateJobBoardRequest($jobBoardRequest->getId()));

                return $this->redirectToRoute('view_request', ['id' => $requestEntity->getId()]);
            }

            if ($form->get('saveAndPreview')->isClicked()) {
                $jobBoardRequest->setPublished(false);
                $jobBoardRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_INACTIVE);
                $jobBoardRequest->setStatusLabel('Inactive job posting');
                $this->entityManager->persist($jobBoardRequest);
                $this->entityManager->flush();

                $status = $this->dispatchMessage(new CreateJobBoardRequest($jobBoardRequest->getId()));

                return $this->redirectToRoute('view_request', ['id' => $requestEntity->getId()]);
            }

            $this->entityManager->persist($requestEntity);
            $this->entityManager->flush();
        }

        return $this->render('request/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/requests/{id}/view", name="view_request", options = { "expose" = true })
     * @param RequestEntity $requestEntity
     * @param Request       $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewRequest(RequestEntity $requestEntity, Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        if ($requestEntity->getParentRequest() && $requestEntity->getCreatedBy()->getId() === $user->getId()) {
            return $this->redirectToRoute('view_request', ['id' => $requestEntity->getParentRequest()->getId()]);
        }

        return $this->render('request/view.html.twig', [
            'user' => $user,
            'request' => $requestEntity,
        ]);
    }

    /**
     * @Route("/requests/{id}/hide", name="hide_request", options = { "expose" = true })
     * @param RequestEntity $requestEntity
     * @param Request       $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function hideRequest(RequestEntity $requestEntity, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $userMeta = new UserMeta();
        $userMeta->setUser($user);
        $userMeta->setName(UserMeta::HIDE_REQUEST);
        $userMeta->setValue($requestEntity->getId());
        $this->entityManager->persist($userMeta);
        $this->entityManager->flush();

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

}
