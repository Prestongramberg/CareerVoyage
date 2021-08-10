<?php

namespace App\Controller;

use App\Entity\AllowedCommunication;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\EducatorRegisterEducatorForCompanyExperienceRequest;
use App\Entity\RequestAction;
use App\Entity\SchoolAdminRegisterSAForCompanyExperienceRequest;
use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\Registration;
use App\Entity\School;
use App\Entity\SchoolExperience;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentToMeetProfessionalRequest;
use App\Entity\TeachLessonExperience;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Entity\UserMeta;
use App\Entity\UserRegisterForSchoolExperienceRequest;
use App\Form\CreateRequestFormType;
use App\Form\EditRequestFormType;
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
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requests(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $requestsByType = [];


        // todo should we change this endpoint to be more of a search-requests endpoint?
        //  then you can pass up ?student-requests=true, ?pending-requests=true and the various filters for each tab?
        //  also you are probably going to need to do a refresh on each tab and make it so it's a page reload.
        //  look at the resources page for how I did this. I'm pretty sure I did it here with the
        //  different videos, etc and tabs at the top of the page


        $reviewRequests = $this->requestRepository->getRequestsThatNeedMyApproval($user);


        // TODO SECOND DRAFT
        /*        $myCreatedRequests = $this->requestRepository->findBy([
                    'created_by' => $user,
                    'denied' => false,
                    'approved' => false,
                    'allowApprovalByActivationCode' => false,
                ], ['createdAt' => 'DESC']);

                $deniedByMeRequests = $this->requestRepository->findBy([
                    'needsApprovalBy' => $user,
                    'denied' => true,
                ], ['createdAt' => 'DESC']);

                $approvedByMeRequests = $this->requestRepository->findBy([
                    'needsApprovalBy' => $user,
                    'approved' => true,
                ], ['createdAt' => 'DESC']);

                $myDeniedAccessRequests = $this->requestRepository->findBy([
                    'created_by' => $user,
                    'denied' => true,
                ], ['createdAt' => 'DESC']);

                $myApprovedAccessRequests = $this->requestRepository->findBy([
                    'created_by' => $user,
                    'approved' => true,
                ], ['createdAt' => 'DESC']);


                $qb = $this->entityManager->createQueryBuilder();
                $qb
                    ->select('r')
                    ->from('App\Entity\Request', 'r')
                    ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
                    ->andWhere('e.studentUser = :user')
                    ->andWhere('r.approved = true')
                    ->setParameter('user', $user)
                    ->groupBy('e.id')
                    ->orderBy('r.createdAt', 'DESC');

                $studentRegisterApproval = $qb->getQuery()->getResult();

                $qb = $this->entityManager->createQueryBuilder();
                $qb
                    ->select('r')
                    ->from('App\Entity\Request', 'r')
                    ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
                    ->andWhere('e.studentUser = :user')
                    ->andWhere('r.denied = true')
                    ->setParameter('user', $user)
                    ->groupBy('e.id')
                    ->orderBy('r.createdAt', 'DESC');

                $studentRegisterDenial = $qb->getQuery()->getResult();

                $qb = $this->entityManager->createQueryBuilder();
                $qb
                    ->select('r')
                    ->from('App\Entity\Request', 'r')
                    ->leftJoin('App\Entity\UserRegisterForSchoolExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
                    ->andWhere('e.user = :user')
                    ->andWhere('r.approved = true')
                    ->setParameter('user', $user)
                    ->groupBy('e.id')
                    ->orderBy('r.createdAt', 'DESC');

                $userRegisterSchoolApproval = $qb->getQuery()->getResult();

                $qb = $this->entityManager->createQueryBuilder();
                $qb
                    ->select('r')
                    ->from('App\Entity\Request', 'r')
                    ->leftJoin('App\Entity\UserRegisterForSchoolExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
                    ->andWhere('e.user = :user')
                    ->andWhere('r.denied = true')
                    ->setParameter('user', $user)
                    ->groupBy('e.id')
                    ->orderBy('r.createdAt', 'DESC');

                $userRegisterSchoolDenial = $qb->getQuery()->getResult();


                */


        // TODO FIRST DRAFT
        // $studentHasSeenCompanyRequestsApproval = [];
        // $studentHasSeenCompanyRequestsDenial = [];
        // if($user->isStudent()) {
        //     $qb = $this->entityManager->createQueryBuilder();
        //     $qb
        //         ->select('r')
        //         ->from('App\Entity\Request', 'r')
        //         ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
        //         ->andWhere('e.studentUser = :user')
        //         ->andWhere('r.approved = true')
        //         ->andWhere('e.studentHasSeen = false')
        //         ->setParameter('user', $user)
        //         ->groupBy('e.id');

        //     $studentHasSeenCompanyRequestsApproval = $qb->getQuery()->getResult();

        //     $qb = $this->entityManager->createQueryBuilder();
        //     $qb
        //         ->select('r')
        //         ->from('App\Entity\Request', 'r')
        //         ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
        //         ->andWhere('e.studentUser = :user')
        //         ->andWhere('r.denied = true')
        //         ->andWhere('e.studentHasSeen = false')
        //         ->setParameter('user', $user)
        //         ->groupBy('e.id');

        //     $studentHasSeenCompanyRequestsDenial = $qb->getQuery()->getResult();
        // }

        // $educatorHasSeenCompanyRequestsApproval = [];
        // $educatorHasSeenCompanyRequestsDenial = [];
        // if($user->isEducator()) {
        //     $qb = $this->entityManager->createQueryBuilder();
        //     $qb
        //         ->select('r')
        //         ->from('App\Entity\Request', 'r')
        //         ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
        //         ->andWhere('r.approved = true')
        //         ->andWhere('e.educatorHasSeen = false')
        //         ->groupBy('e.id');

        //     $educatorHasSeenCompanyRequestsApproval = $qb->getQuery()->getResult();

        //     $qb = $this->entityManager->createQueryBuilder();
        //     $qb
        //         ->select('r')
        //         ->from('App\Entity\Request', 'r')
        //         ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
        //         ->andWhere('r.denied = true')
        //         ->andWhere('e.educatorHasSeen = false')
        //         ->groupBy('e.id');

        //     $educatorHasSeenCompanyRequestsDenial = $qb->getQuery()->getResult();
        // }

        // $professionalHasSeenCompanyRequestsApproval = [];
        // $professionalHasSeenCompanyRequestsDenial = [];
        // if($user->isProfessional()) {
        //     $qb = $this->entityManager->createQueryBuilder();
        //     $qb
        //         ->select('r')
        //         ->from('App\Entity\Request', 'r')
        //         ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
        //         ->andWhere('r.approved = true')
        //         ->andWhere('e.professionalHasSeen = false')
        //         ->groupBy('e.id');

        //     $professionalHasSeenCompanyRequestsApproval = $qb->getQuery()->getResult();

        //     $qb = $this->entityManager->createQueryBuilder();
        //     $qb
        //         ->select('r')
        //         ->from('App\Entity\Request', 'r')
        //         ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
        //         ->andWhere('r.denied = true')
        //         ->andWhere('e.professionalHasSeen = false')
        //         ->groupBy('e.id');

        //     $professionalHasSeenCompanyRequestsDenial = $qb->getQuery()->getResult();
        // }

        // todo you could return a different view per user role as well
        return $this->render('request/index_new.html.twig', [
            'user' => $user,
            'reviewRequests' => $reviewRequests,


            /*'requestsThatNeedMyApproval' => $requestsThatNeedMyApproval,
            'myCreatedRequests' => $myCreatedRequests,
            'approvedByMeRequests' => $approvedByMeRequests,
            'deniedByMeRequests' => $deniedByMeRequests,
            'myApprovedAccessRequests' => $myApprovedAccessRequests,
            'myDeniedAccessRequests' => $myDeniedAccessRequests,
            'studentRegisterApproval' => $studentRegisterApproval,
            'studentRegisterDenial' => $studentRegisterDenial,
            'userRegisterSchoolApproval' => $userRegisterSchoolApproval,
            'userRegisterSchoolDenial' => $userRegisterSchoolDenial*/


            // 'studentHasSeenCompanyRequestsApproval' => count($studentHasSeenCompanyRequestsApproval),
            // 'studentHasSeenCompanyRequestsDenial' => count($studentHasSeenCompanyRequestsDenial),
            // 'educatorHasSeenCompanyRequestsApproval' => count($educatorHasSeenCompanyRequestsApproval),
            // 'educatorHasSeenCompanyRequestsDenial' => count($educatorHasSeenCompanyRequestsDenial),
            // 'professionalHasSeenCompanyRequestsApproval' => count($professionalHasSeenCompanyRequestsApproval),
            // 'professionalHasSeenCompanyRequestsDenial' => count($professionalHasSeenCompanyRequestsDenial)
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
        $loggedInUser = $this->getUser();
        $requestId = $httpRequest->query->get('request_id');
        $request   = $this->requestRepository->find($requestId);

        if (!$request) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        // todo if it's a get request and it's ajax return the modal
        // todo if it's a get request and it's not ajax return a normal page?

        switch ($request->getRequestType()) {
            case \App\Entity\Request::REQUEST_TYPE_NEW_COMPANY:

                $companyId = $httpRequest->query->get('company_id');
                $company   = $this->companyRepository->find($companyId);
                $template  = 'request/modal/new_company.html.twig';
                $context   = [
                    'request' => $request,
                    'company' => $company,
                ];

                break;
            default:
                $template = 'request/modal/default.html.twig';
                $context  = [
                    'request' => $request,
                ];
                break;
        }

        if ($httpRequest->getMethod() === 'POST') {

            $action = $httpRequest->query->get('action');

            switch ($action) {

                case RequestAction::REQUEST_ACTION_NAME_APPROVE:

                    // todo What happens on the request management page if you hit approve then
                    //  deny then approve then deny. Which action do we stick with/show?
                    //  should we delete any old request deny actions?

                    $requestAction = new RequestAction();
                    $requestAction->setUser($loggedInUser);
                    $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_APPROVE);
                    $requestAction->setRequest($request);
                    $this->entityManager->persist($requestAction);
                    $this->entityManager->flush();

                    // todo I should return a JSONResponse here or perform a redirect right?

                    break;
                case RequestAction::REQUEST_ACTION_NAME_DENY:

                    // todo What happens on the request management page if you hit approve then
                    //  deny then approve then deny. Which action do we stick with/show?
                    //  should we delete any old request approve actions?

                    $requestAction = new RequestAction();
                    $requestAction->setUser($loggedInUser);
                    $requestAction->setName(RequestAction::REQUEST_ACTION_NAME_DENY);
                    $requestAction->setRequest($request);
                    $this->entityManager->persist($requestAction);
                    $this->entityManager->flush();

                    break;
                default:
                    // TODO???
                    break;
            }
        }

        return new JsonResponse(
            [
                'formMarkup' => $this->renderView($template, $context),
            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/requests/{id}/approve", name="approve_request", methods={"POST"}, options = { "expose" = true })
     * @param \App\Entity\Request $request
     * @param Request             $httpRequest
     *
     * @return RedirectResponse|Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function approveRequest(\App\Entity\Request $request, Request $httpRequest)
    {


        $session = new Session();


        // $this->denyAccessUnlessGranted('edit', $request);

        /** @var User $user */
        $user = $this->getUser();

        $this->handleRequestApproval($request, $httpRequest);

        if ($httpRequest->isXmlHttpRequest()) {
            $flashbag = $session->getFlashBag()->all();
            $flash    = [];
            foreach ($flashbag as $type => $messages) {
                foreach ($messages as $message) {
                    $flash = ["type" => $type, "message" => $message];
                }
            }

            return new JsonResponse(["status" => $flash]);
        }

        $referer = $httpRequest->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * @Route("/requests/{id}/deny", name="deny_request", methods={"POST"}, options = { "expose" = true })
     * @param \App\Entity\Request $request
     * @param Request             $httpRequest
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function denyRequest(\App\Entity\Request $request, Request $httpRequest)
    {

        $session = new Session();

        $this->denyAccessUnlessGranted('edit', $request);


        switch ($request->getClassName()) {
            case 'TeachLessonRequest':
                // not all educators have an email address.
                if ($request->getCreatedBy()->getEmail()) {
                    $this->requestsMailer->teachLessonRequestDenied($request);
                }
                break;
        }

        $request->setDenied(true);
        $request->setEducatorHasSeen(false);
        $this->entityManager->persist($request);
        $this->entityManager->flush();
        $this->addFlash('success', 'Request denied.');

        if ($httpRequest->isXmlHttpRequest()) {
            $flashbag = $session->getFlashBag()->all();
            $flash    = [];
            foreach ($flashbag as $type => $messages) {
                foreach ($messages as $message) {
                    $flash = ["type" => $type, "message" => $message];
                }
            }

            return new JsonResponse(["status" => $flash]);
        }

        $referer = $httpRequest->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * @param \App\Entity\Request $request
     * @param Request             $httpRequest
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function handleRequestApproval(\App\Entity\Request $request, Request $httpRequest)
    {

        switch ($request->getClassName()) {
            case 'NewCompanyRequest':
                /** @var NewCompanyRequest $request */
                $request->setApproved(true);
                $company = $request->getCompany();
                $company->setApproved(true);
                $this->entityManager->persist($company);
                $this->addFlash('success', 'Company approved');
                $this->requestsMailer->newCompanyRequestApproval($request);
                break;
            case 'JoinCompanyRequest':
                /** @var JoinCompanyRequest $request */
                $request->setApproved(true);
                if ($request->getIsFromCompany()) {
                    /** @var ProfessionalUser $needsApprovalBy */
                    $needsApprovalBy = $request->getNeedsApprovalBy();
                    $needsApprovalBy->setupAsProfessional();
                    $needsApprovalBy->setCompany($request->getCompany());
                    $needsApprovalBy->agreeToTerms();
                    $this->entityManager->persist($needsApprovalBy);
                    $this->addFlash('success', 'You have joined the company!');
                } else {
                    /** @var ProfessionalUser $createdBy */
                    $createdBy = $request->getCreatedBy();
                    $createdBy->setupAsProfessional();
                    $createdBy->setCompany($request->getCompany());
                    $createdBy->agreeToTerms();
                    $this->entityManager->persist($createdBy);
                    $this->addFlash('success', 'User successfully added to company!');
                }
                $this->requestsMailer->joinCompanyRequestApproval($request);
                break;
            case 'TeachLessonRequest':
                /** @var TeachLessonRequest $request */
                $request->setApproved(true);
                /** @var ProfessionalUser $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();

                $date = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('date'));
                // we must have an end date so let's just set it for 2 hours from the start
                $endDate = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('date'));
                $endDate->add(new DateInterval('PT2H'));
                $teachLessonExperience = new TeachLessonExperience();
                $teachLessonExperience->setStartDateAndTime($date);
                $teachLessonExperience->setEndDateAndTime($endDate);
                $teachLessonExperience->setTitle(sprintf("
                Topics: %s with Guest Instructor %s, %s in %s Class
                ", $request->getLesson()->getTitle(), $needsApprovalBy->getFullName(), date_format($date, 'F jS Y'), $request->getCreatedBy()->getFullName()));
                $teachLessonExperience->setBriefDescription(sprintf("%s", $request->getLesson()->getShortDescription()));
                $teachLessonExperience->setOriginalRequest($request);


                // let's go ahead and add the professional as a registration on this event
                $professionalRegistration = new Registration();
                $educatorRegistration     = new Registration();
                if ($request->getIsFromProfessional()) {
                    $professionalRegistration->setUser($request->getCreatedBy());
                    $educatorRegistration->setUser($request->getNeedsApprovalBy());
                    $teachLessonExperience->setTeacher($request->getCreatedBy());
                    $teachLessonExperience->setSchool($request->getNeedsApprovalBy()->getSchool());
                } else {
                    $professionalRegistration->setUser($request->getNeedsApprovalBy());
                    $educatorRegistration->setUser($request->getCreatedBy());
                    $teachLessonExperience->setTeacher($request->getNeedsApprovalBy());
                    $teachLessonExperience->setSchool($request->getCreatedBy()->getSchool());
                }

                $professionalRegistration->setExperience($teachLessonExperience);
                $educatorRegistration->setExperience($teachLessonExperience);
                $this->entityManager->persist($professionalRegistration);
                $this->entityManager->persist($educatorRegistration);

                // let's go ahead and add the students as registrations to the event
                if ($request->getIsFromProfessional()) {
                    /** @var EducatorUser $educator */
                    $educator = $request->getNeedsApprovalBy();
                } else {
                    /** @var EducatorUser $educator */
                    $educator = $request->getCreatedBy();
                }
                foreach ($educator->getStudentUsers() as $studentUser) {
                    $studentRegistration = new Registration();
                    $studentRegistration->setExperience($teachLessonExperience);
                    $studentRegistration->setUser($studentUser);
                    $this->entityManager->persist($studentRegistration);
                }

                /** @var School $school */
                $school = $request->getCreatedBy()->getSchool();

                // the CSV school import fixtures did not have emails so we need to check for them!
                if ($school->getEmail()) {
                    $teachLessonExperience->setEmail($school->getEmail());
                }

                if ($school->getStreet()) {
                    $teachLessonExperience->setStreet($school->getStreet());
                }

                if ($school->getCity()) {
                    $teachLessonExperience->setCity($school->getCity());
                }

                if ($school->getState()) {
                    $teachLessonExperience->setState($school->getState());
                }

                if ($school->getZipcode()) {
                    $teachLessonExperience->setZipcode($school->getZipcode());
                }

                $this->entityManager->persist($teachLessonExperience);
                $this->addFlash('success', 'You have accepted the invite to teach!');

                // not all educators have an email address.
                if ($request->getCreatedBy()->getEmail()) {
                    $this->requestsMailer->teachLessonRequestApproval($request);
                }
                break;
            case 'EducatorRegisterStudentForCompanyExperienceRequest':
                /** @var EducatorRegisterStudentForCompanyExperienceRequest $request */
                $studentUser = $request->getStudentUser();
                $experience  = $request->getCompanyExperience();

                if ($experience->getAvailableSpaces() === 0) {
                    $this->addFlash('error', 'Could not approve registration. 0 spots left.');
                }

                if ($experience->getAvailableSpaces() !== 0) {
                    $experience->setAvailableSpaces($experience->getAvailableSpaces() - 1);
                }
                $this->entityManager->persist($experience);
                $request->setApproved(true);
                $this->entityManager->persist($request);
                $registration = new Registration();
                $registration->setUser($studentUser);
                $registration->setExperience($request->getCompanyExperience());
                $this->entityManager->persist($registration);
                // make sure the teacher has a registration as well
                $previousTeacherRegistration = $this->registrationRepository->getByUserAndExperience($request->getCreatedBy(), $request->getCompanyExperience());
                if (!$previousTeacherRegistration) {
                    $registration = new Registration();
                    $registration->setUser($request->getCreatedBy());
                    $registration->setExperience($request->getCompanyExperience());
                    $this->entityManager->persist($registration);
                }
                // an educator who created the request might not have an email
                if ($request->getCreatedBy()->getEmail()) {
                    $this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequestApproval($request);
                }

                if ($request->getStudentUser()->getEmail()) {
                    $this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequestApprovalEmailForStudent($request);
                }

                $this->addFlash('success', 'Students have been registered in event!');
                $this->entityManager->flush();
                break;
            case 'EducatorRegisterEducatorForCompanyExperienceRequest':
                /** @var EducatorRegisterEducatorForCompanyExperienceRequest $request */
                $educatorUser = $request->getEducatorUser();
                $experience   = $request->getCompanyExperience();

                $this->entityManager->persist($experience);
                $request->setApproved(true);
                $this->entityManager->persist($request);
                $registration = new Registration();
                $registration->setUser($educatorUser);
                $registration->setExperience($request->getCompanyExperience());
                $this->entityManager->persist($registration);
                // make sure the teacher has a registration as well

                $this->addFlash('success', 'You have been registered for this event!');
                $this->entityManager->flush();
                break;
            case 'SchoolAdminRegisterSAForCompanyExperienceRequest':
                /** @var SchoolAdminRegisterSAForCompanyExperienceRequest $request */
                $schoolAdminUser = $request->getSchoolAdminUser();
                $experience      = $request->getCompanyExperience();

                $this->entityManager->persist($experience);
                $request->setApproved(true);
                $this->entityManager->persist($request);
                $registration = new Registration();
                $registration->setUser($schoolAdminUser);
                $registration->setExperience($request->getCompanyExperience());
                $this->entityManager->persist($registration);
                // make sure the teacher has a registration as well

                $this->addFlash('success', 'You have been registered for this event!');
                $this->entityManager->flush();
                break;
            case 'StudentToMeetProfessionalRequest':
                /** @var StudentToMeetProfessionalRequest $request */
                $student      = $request->getStudent();
                $professional = $request->getProfessional();
                $request->setApproved(true);
                $reasonToMeet = $request->getReasonToMeet();
                if ($httpRequest->request->has('isFromEducator')) {
                    // if the request is from the educator this means teacher approval was required and they have approved
                    // next thing you need to do is create a request to be sent to the professional
                    $newRequest = new StudentToMeetProfessionalRequest();
                    $newRequest->setCreatedBy($request->getNeedsApprovalBy());
                    $newRequest->setProfessional($professional);
                    $newRequest->setStudent($student);
                    $newRequest->setReasonToMeet($reasonToMeet);
                    $newRequest->setNeedsApprovalBy($professional);
                    $this->entityManager->persist($newRequest);
                    $this->addFlash('success', 'Request being sent to professional to setup 3 dates to meet with student!');
                    $this->requestsMailer->studentToMeetProfessionalApproval($newRequest);
                }
                if ($httpRequest->request->has('isFromProfessional')) {
                    // if the request is from the professional send off the next request to the student to finalize the date
                    $dateOptionOne   = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('dateOptionOne'));
                    $dateOptionTwo   = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('dateOptionTwo'));
                    $dateOptionThree = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('dateOptionThree'));
                    $newRequest      = new StudentToMeetProfessionalRequest();
                    $newRequest->setDateOptionOne($dateOptionOne);
                    $newRequest->setDateOptionTwo($dateOptionTwo);
                    $newRequest->setDateOptionThree($dateOptionThree);
                    $newRequest->setStudent($student);
                    $newRequest->setReasonToMeet($reasonToMeet);
                    $newRequest->setProfessional($professional);
                    $newRequest->setNeedsApprovalBy($student);
                    $newRequest->setCreatedBy($professional);
                    $this->entityManager->persist($newRequest);
                    $this->addFlash('success', 'Request sent to student to finalize one of your three dates!');
                    $this->requestsMailer->studentToMeetProfessionalApproval($newRequest);
                }

                if ($httpRequest->request->has('isFromStudent')) {
                    // if the request is from the student then they are approving the final date. Go ahead and add it to both the
                    // students calendar and professionals calendar by creating a new experience object for both of them.
                    $date = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('date'));
                    // we must have an end date so let's just set it for 2 hours from the start
                    $endDate = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('date'));
                    $endDate->add(new DateInterval('PT2H'));
                    $request->setConfirmedDate($date);
                    $descriptionDate = date_format($date, 'F jS Y');

                    $experience = new StudentToMeetProfessionalExperience();
                    $experience->setOriginalRequest($request);
                    $experience->setStartDateAndTime($date);
                    $experience->setEndDateAndTime($endDate);
                    $experience->setTitle(sprintf("Student %s to meet with Professional %s for %s, %s",
                        $request->getNeedsApprovalBy()->getFullName(),
                        $request->getCreatedBy()->getFullName(),
                        $request->getReasonToMeet()->getEventName(),
                        $descriptionDate
                    ));
                    $experience->setBriefDescription("Student to meet Professional");

                    $experience->setOriginalRequest($request);
                    $this->entityManager->persist($experience);
                    // student registration for event
                    $studentRegistration = new Registration();
                    $studentRegistration->setUser($request->getNeedsApprovalBy());
                    $studentRegistration->setExperience($experience);
                    $this->entityManager->persist($studentRegistration);
                    // teacher registration for event
                    $teacherRegistration = new Registration();
                    $teacherRegistration->setUser($request->getCreatedBy());
                    $teacherRegistration->setExperience($experience);
                    $this->entityManager->persist($teacherRegistration);
                    $this->addFlash('success', 'Request successfully confirmed! This experience will be added to yours and the professionals calendar.');
                    // The whole process has been completed successfully. Now you need to open up the line of communication between the student and professional
                    $allowedCommunication = new AllowedCommunication();
                    $allowedCommunication->setStudentUser($student);
                    $allowedCommunication->setProfessionalUser($professional);
                    $this->entityManager->persist($allowedCommunication);
                    $this->requestsMailer->studentToMeetProfessionalFinalDateConfirmed($request);
                }

                // todo make sure we send emails
                $this->entityManager->flush();
                break;
            case 'UserRegisterForSchoolExperienceRequest':
                /** @var UserRegisterForSchoolExperienceRequest $request */
                /** @var User $user */
                $user = $request->getUser();
                /** @var SchoolExperience $experience */
                $experience = $request->getSchoolExperience();
                if ($user->isProfessional() && $experience->getAvailableProfessionalSpaces() === 0) {
                    $this->addFlash('error', 'Could not approve registration. 0 spots left.');
                }
                if (($user->isStudent() || $user->isEducator()) && $experience->getAvailableStudentSpaces() === 0) {
                    $this->addFlash('error', 'Could not approve registration. 0 spots left.');
                }

                if ($user->isProfessional() && $experience->getAvailableProfessionalSpaces() !== 0) {
                    $experience->setAvailableProfessionalSpaces($experience->getAvailableProfessionalSpaces() - 1);

                }
                if (($user->isStudent() || $user->isEducator()) && $experience->getAvailableStudentSpaces() !== 0) {
                    $experience->setAvailableStudentSpaces($experience->getAvailableStudentSpaces() - 1);
                }

                $request->setApproved(true);
                $this->entityManager->persist($request);
                $this->entityManager->persist($experience);
                $registration = new Registration();
                $registration->setUser($user);
                $registration->setExperience($experience);
                $this->entityManager->persist($registration);
                $this->requestsMailer->userRegisterForSchoolExperienceRequestApproval($request);
                $this->addFlash('success', 'User has been registered for event!');
                $this->entityManager->flush();
                break;
        }
        $this->entityManager->persist($request);
        $this->entityManager->flush();
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
     */
    public function studentRequestToMeetProfessionalAction(Request $request)
    {
        $studentId      = $request->request->get('studentId');
        $student        = $this->studentUserRepository->find($studentId);
        $professionalId = $request->request->get('professionalId');
        $professional   = $this->professionalUserRepository->find($professionalId);
        $reasonToMeet   = $request->request->get('reasonToMeet');
        $reasonToMeet   = $this->rolesWillingToFulfillRepository->findOneBy([
            'eventName' => $reasonToMeet,
        ]);
        // let's determine who gets the first request. Professional or the Educator
        $requestEntity = new StudentToMeetProfessionalRequest();
        if ($student->isCommunicationEnabled() && $student->isTeacherApprovalNotRequired()) {
            $requestEntity->initializeForProfessional($student, $professional, $reasonToMeet);
        } elseif ($student->isCommunicationEnabled() && $student->isTeacherApprovalRequired()) {
            $teachers = $student->getEducatorUsers();
            if (count($teachers) === 0) {
                $this->addFlash('error', 'You must have at least one educator setup in your profile to perform this action.');

                return $this->redirectToRoute('profile_index', ['id' => $professional->getId()]);
            }
            // todo we might need to refactor this so there is a designated teacher that receives the request
            //  right now just sending the request to the first teacher in the collection
            $requestEntity->initializeForEducator($student, $professional, $teachers[0], $reasonToMeet);
        }
        $this->requestsMailer->studentToMeetProfessionalApproval($requestEntity);
        $this->entityManager->persist($requestEntity);
        $this->entityManager->flush();
        /*$this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequest($registerRequest);*/
        $this->addFlash('success', 'Request to meet successfully sent.');

        return $this->redirectToRoute('profile_index', ['id' => $professional->getId()]);
    }

    /**
     * @Route("/requests/{id}/user_has_seen_request", name="user_has_seen_request", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
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

        $requestEntity = new RequestEntity();
        $requestEntity->setRequestType(RequestEntity::REQUEST_TYPE_JOB_BOARD);

        $form = $this->createForm(CreateRequestFormType::class, $requestEntity, [
            'skip_validation' => $request->request->get('skip_validation', false),
        ]);

        $form->handleRequest($request);

        if ($form->get('delete')->isClicked()) {
            $this->addFlash('success', 'Your request has been deleted.');

            return $this->redirectToRoute('new_request');
        }

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var RequestEntity $requestEntity */
            $requestEntity = $form->getData();
            $requestEntity->setCreatedBy($user);

            if ($form->get('postAndReview')->isClicked()) {

                $requestEntity->setPublished(true);
                $this->entityManager->persist($requestEntity);
                $this->entityManager->flush();

                return $this->redirectToRoute('view_request', ['id' => $requestEntity->getId()]);
            }

            if ($form->get('saveAndPreview')->isClicked()) {
                $requestEntity->setPublished(false);
                $this->entityManager->persist($requestEntity);
                $this->entityManager->flush();

                return $this->redirectToRoute('view_request', ['id' => $requestEntity->getId()]);
            }

            $this->entityManager->persist($requestEntity);
            $this->entityManager->flush();
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
     */
    public function editRequest(RequestEntity $requestEntity, Request $request)
    {
        /** @var User $user */
        $user    = $this->getUser();
        $publish = $request->query->get('publish', null);

        $accessDenied = (
            $requestEntity->getCreatedBy() &&
            $requestEntity->getCreatedBy()->getId() !== $user->getId()
        );

        if ($accessDenied) {
            throw new AccessDeniedException();
        }

        if ($publish) {
            $requestEntity->setPublished(true);
            $this->entityManager->persist($requestEntity);
            $this->entityManager->flush();

            return $this->redirectToRoute('view_request', ['id' => $requestEntity->getId()]);
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

            /** @var RequestEntity $requestEntity */
            $requestEntity = $form->getData();

            if ($form->get('postAndReview')->isClicked()) {

                $requestEntity->setPublished(true);
                $this->entityManager->persist($requestEntity);
                $this->entityManager->flush();

                return $this->redirectToRoute('view_request', ['id' => $requestEntity->getId()]);
            }

            if ($form->get('saveAndPreview')->isClicked()) {
                $requestEntity->setPublished(false);
                $this->entityManager->persist($requestEntity);
                $this->entityManager->flush();

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

        $user = $this->getUser();

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
