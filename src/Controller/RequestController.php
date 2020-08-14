<?php

namespace App\Controller;

use App\Entity\AllowedCommunication;
use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\Registration;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentToMeetProfessionalRequest;
use App\Entity\TeachLessonExperience;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Entity\UserRegisterForSchoolExperienceRequest;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Mailer\RequestsMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\EducatorRegisterStudentForExperienceRequestRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\NewCompanyRequestRepository;
use App\Repository\RegistrationRepository;
use App\Repository\RequestRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Class RequestController
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requests(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $requestsByType = [];

        $requestsThatNeedMyApproval = $this->requestRepository->getRequestsThatNeedMyApproval($user);

        $myCreatedRequests = $this->requestRepository->findBy([
            'created_by' => $user,
            'denied' => false,
            'approved' => false,
            'allowApprovalByActivationCode' => false
        ], ['createdAt' => 'DESC']);

        $deniedByMeRequests = $this->requestRepository->findBy([
            'needsApprovalBy' => $user,
            'denied' => true,
        ], ['createdAt' => 'DESC']);

        $myDeniedAccessRequests = $this->requestRepository->findBy([
            'created_by' => $user,
            'denied' => true,
        ], ['createdAt' => 'DESC']);

        $approvedByMeRequests = $this->requestRepository->findBy([
            'needsApprovalBy' => $user,
            'approved' => true,
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
            ->andWhere('r.approved = true')
            ->andWhere('e.studentHasSeen = false')
            ->setParameter('user', $user)
            ->groupBy('e.id');

        $studentHasSeenCompanyRequestsApproval = $qb->getQuery()->getResult();

        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('r')
            ->from('App\Entity\Request', 'r')
            ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
            ->andWhere('e.studentUser = :user')
            ->andWhere('r.approved = false')
            ->andWhere('e.studentHasSeen = false')
            ->setParameter('user', $user)
            ->groupBy('e.id');

        $studentHasSeenCompanyRequestsDenial = $qb->getQuery()->getResult();


        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('r')
            ->from('App\Entity\Request', 'r')
            ->leftJoin('App\Entity\EducatorRegisterStudentForCompanyExperienceRequest', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.id')
            ->andWhere('e.studentUser = :user')
            ->andWhere('r.approved = false')
            ->setParameter('user', $user)
            ->groupBy('e.id')
            ->orderBy('r.createdAt', 'DESC');

        $studentRegisterDenial = $qb->getQuery()->getResult();

        // todo you could return a different view per user role as well
        return $this->render('request/index.html.twig', [
            'user' => $user,
            'requestsThatNeedMyApproval' => $requestsThatNeedMyApproval,
            'myCreatedRequests' => $myCreatedRequests,
            'deniedByMeRequests' => $deniedByMeRequests,
            'myDeniedAccessRequests' => $myDeniedAccessRequests,
            'approvedByMeRequests' => $approvedByMeRequests,
            'myApprovedAccessRequests' => $myApprovedAccessRequests,
            'studentRegisterApproval' => $studentRegisterApproval,
            'studentRegisterDenial' => $studentRegisterDenial,
            'studentHasSeenCompanyRequestsApproval' => count($studentHasSeenCompanyRequestsApproval),
            'studentHasSeenCompanyRequestsDenial' => count($studentHasSeenCompanyRequestsDenial)
        ]);
    }

    /**
     * @Route("/requests/{id}/approve", name="approve_request", methods={"POST"}, options = { "expose" = true })
     * @param \App\Entity\Request $request
     * @param Request $httpRequest
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function approveRequest(\App\Entity\Request $request, Request $httpRequest) {


        $session = new Session();


        $this->denyAccessUnlessGranted('edit', $request);

        /** @var User $user */
        $user = $this->getUser();

        $this->handleRequestApproval($request, $httpRequest);

        // return $this->redirectToRoute('requests');
        // $flashbag = $this->get('session')->getFlashBag();
        $flashbag = $session->getFlashBag()->all();
        $flash = [];
        foreach($flashbag as $type => $messages){
            foreach($messages as $message){
                array_push($flash, ["type" => $type, "message" => $message]);
            }
        }
        
        return new JsonResponse( ["status" => json_encode($flash) ]);

        // if(sizeof($flashbag->get('success')) > 0){
        //     return new JsonResponse( ["status" => $flashbag->peek('success') ]);
        // } else {
        //     return new JsonResponse( ["status" => $flashbag->peek('error') ]);
        // }
    }

    /**
     * @Route("/requests/{id}/deny", name="deny_request", methods={"POST"}, options = { "expose" = true })
     * @param \App\Entity\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function denyRequest(\App\Entity\Request $request) {

        $session = new Session();

        $this->denyAccessUnlessGranted('edit', $request);


        switch($request->getClassName()) {
            case 'TeachLessonRequest':
                // not all educators have an email address.
                if($request->getCreatedBy()->getEmail()) {
                    $this->requestsMailer->teachLessonRequestDenied($request);
                }
                break;
        }

        $request->setDenied(true);
        $this->entityManager->persist($request);
        $this->entityManager->flush();
        $this->addFlash('success', 'Request denied.');

        $flashbag = $session->getFlashBag()->all();
        $flash = [];
        foreach($flashbag as $type => $messages){
            foreach($messages as $message){
                array_push($flash, ["type" => $type, "message" => $message]);
            }
        }
        
        return new JsonResponse( ["status" => json_encode($flash) ]);

        // return new JsonResponse( ["status" => "success", "flash" => "Request denied."]);

    }

    /**
     * @param \App\Entity\Request $request
     * @param Request $httpRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function handleRequestApproval(\App\Entity\Request $request, Request $httpRequest) {

        switch($request->getClassName()) {
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
                if($request->getIsFromCompany()) {
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
                Lesson: %s with Guest Instructor %s, %s in %s Class
                ", $request->getLesson()->getTitle(), $needsApprovalBy->getFullName(), date_format($date,'F jS Y'), $request->getCreatedBy()->getFullName()));
                $teachLessonExperience->setBriefDescription(sprintf("%s", $request->getLesson()->getShortDescription()));
                $teachLessonExperience->setOriginalRequest($request);


                // let's go ahead and add the professional as a registration on this event
                $professionalRegistration = new Registration();
                $educatorRegistration = new Registration();
                if($request->getIsFromProfessional()) {
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
                if($request->getIsFromProfessional()) {
                    /** @var EducatorUser $educator */
                    $educator = $request->getNeedsApprovalBy();
                } else {
                    /** @var EducatorUser $educator */
                    $educator = $request->getCreatedBy();
                }
                foreach($educator->getStudentUsers() as $studentUser) {
                    $studentRegistration = new Registration();
                    $studentRegistration->setExperience($teachLessonExperience);
                    $studentRegistration->setUser($studentUser);
                    $this->entityManager->persist($studentRegistration);
                }

                /** @var School $school */
                $school = $request->getCreatedBy()->getSchool();

                // the CSV school import fixtures did not have emails so we need to check for them!
                if($school->getEmail()) {
                    $teachLessonExperience->setEmail($school->getEmail());
                }

                if($school->getStreet()) {
                    $teachLessonExperience->setStreet($school->getStreet());
                }

                if($school->getCity()) {
                    $teachLessonExperience->setCity($school->getCity());
                }

                if($school->getState()) {
                    $teachLessonExperience->setState($school->getState());
                }

                if($school->getZipcode()) {
                    $teachLessonExperience->setZipcode($school->getZipcode());
                }

                $this->entityManager->persist($teachLessonExperience);
                $this->addFlash('success', 'You have accepted the invite to teach!');

                // not all educators have an email address.
                if($request->getCreatedBy()->getEmail()) {
                    $this->requestsMailer->teachLessonRequestApproval($request);
                }
                break;
            case 'EducatorRegisterStudentForCompanyExperienceRequest':
                /** @var EducatorRegisterStudentForCompanyExperienceRequest $request */
                $studentUser = $request->getStudentUser();
                $experience = $request->getCompanyExperience();

                if($experience->getAvailableSpaces() === 0) {
                    $this->addFlash('error', 'Could not approve registration. 0 spots left.');
                }

                if($experience->getAvailableSpaces() !== 0) {
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
                if(!$previousTeacherRegistration) {
                    $registration = new Registration();
                    $registration->setUser($request->getCreatedBy());
                    $registration->setExperience($request->getCompanyExperience());
                    $this->entityManager->persist($registration);
                }
                // an educator who created the request might not have an email
                if($request->getCreatedBy()->getEmail()) {
                    $this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequestApproval($request);
                }

                if($request->getStudentUser()->getEmail()) {
                    $this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequestApprovalEmailForStudent($request);
                }

                $this->addFlash('success', 'Students have been registered in event!');
                $this->entityManager->flush();
                break;
            case 'StudentToMeetProfessionalRequest':
                /** @var StudentToMeetProfessionalRequest $request */
                $student = $request->getStudent();
                $professional = $request->getProfessional();
                $request->setApproved(true);
                $reasonToMeet = $request->getReasonToMeet();
                if($httpRequest->request->has('isFromEducator')) {
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
                if($httpRequest->request->has('isFromProfessional')) {
                    // if the request is from the professional send off the next request to the student to finalize the date
                    $dateOptionOne = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('dateOptionOne'));
                    $dateOptionTwo = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('dateOptionTwo'));
                    $dateOptionThree = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('dateOptionThree'));
                    $newRequest = new StudentToMeetProfessionalRequest();
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

                if($httpRequest->request->has('isFromStudent')) {
                    // if the request is from the student then they are approving the final date. Go ahead and add it to both the
                    // students calendar and professionals calendar by creating a new experience object for both of them.
                    $date = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('date'));
                    // we must have an end date so let's just set it for 2 hours from the start
                    $endDate = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('date'));
                    $endDate->add(new DateInterval('PT2H'));
                    $request->setConfirmedDate($date);
                    $descriptionDate = date_format($date,'F jS Y');

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
                if($user->isProfessional() && $experience->getAvailableProfessionalSpaces() === 0) {
                    $this->addFlash('error', 'Could not approve registration. 0 spots left.');
                }
                if($user->isStudent() && $experience->getAvailableStudentSpaces() === 0) {
                    $this->addFlash('error', 'Could not approve registration. 0 spots left.');
                }

                if($user->isProfessional() && $experience->getAvailableProfessionalSpaces() !== 0) {
                    $experience->setAvailableProfessionalSpaces($experience->getAvailableProfessionalSpaces() - 1);

                }
                if($user->isStudent() && $experience->getAvailableStudentSpaces() !== 0) {
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function studentRequestToMeetProfessionalAction(Request $request) {
        $studentId = $request->request->get('studentId');
        $student = $this->studentUserRepository->find($studentId);
        $professionalId = $request->request->get('professionalId');
        $professional = $this->professionalUserRepository->find($professionalId);
        $reasonToMeet = $request->request->get('reasonToMeet');
        $reasonToMeet = $this->rolesWillingToFulfillRepository->findOneBy([
           'eventName' => $reasonToMeet
        ]);
        // let's determine who gets the first request. Professional or the Educator
        $request = new StudentToMeetProfessionalRequest();
        if($student->isCommunicationEnabled() && $student->isTeacherApprovalNotRequired()) {
            $request->initializeForProfessional($student, $professional,  $reasonToMeet);
        } elseif ($student->isCommunicationEnabled() && $student->isTeacherApprovalRequired()) {
            $teachers = $student->getEducatorUsers();
            if(count($teachers) === 0) {
                $this->addFlash('error', 'You must have at least one educator setup in your profile to perform this action.');
                return $this->redirectToRoute('profile_index', ['id' => $professional->getId()]);
            }
            // todo we might need to refactor this so there is a designated teacher that receives the request
            //  right now just sending the request to the first teacher in the collection
            $request->initializeForEducator($student, $professional, $teachers[0], $reasonToMeet);
        }
        $this->requestsMailer->studentToMeetProfessionalApproval($request);
        $this->entityManager->persist($request);
        $this->entityManager->flush();
        /*$this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequest($registerRequest);*/
        $this->addFlash('success', 'Request to meet successfully sent.');
        return $this->redirectToRoute('profile_index', ['id' => $professional->getId()]);
    }

    /**
     * @Route("/requests/{id}/student_has_seen_request", name="student_has_seen_request", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function toggleHasStudentSeenRequest(\App\Entity\EducatorRegisterStudentForCompanyExperienceRequest $request) {
        // $request_id = $request->request->get('id');
        $request->setStudentHasSeen(true);
        $this->entityManager->persist($request);
        $this->entityManager->flush();
        
        return new JsonResponse(
            Response::HTTP_OK
        );
    }

}
