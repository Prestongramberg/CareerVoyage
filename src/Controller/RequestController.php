<?php

namespace App\Controller;

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
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\TeachLessonExperience;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
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

/**
 * Class RequestController
 * @package App\Controller
 * @Route("/dashboard")
 */
class RequestController extends AbstractController
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
     * @var NewCompanyRequestRepository
     */
    private $newCompanyRequestRepository;

    /**
     * @var JoinCompanyRequestRepository
     */
    private $joinCompanyRequestRepository;

    /**
     * @var RequestRepository
     */
    private $requestRepository;

    /**
     * @var RequestsMailer
     */
    private $requestsMailer;

    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository
     */
    private $educatorRegisterStudentForExperienceRequestRepository;

    /**
     * RequestController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param NewCompanyRequestRepository $newCompanyRequestRepository
     * @param JoinCompanyRequestRepository $joinCompanyRequestRepository
     * @param RequestRepository $requestRepository
     * @param RequestsMailer $requestsMailer
     * @param RegistrationRepository $registrationRepository
     * @param EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository
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
        NewCompanyRequestRepository $newCompanyRequestRepository,
        JoinCompanyRequestRepository $joinCompanyRequestRepository,
        RequestRepository $requestRepository,
        RequestsMailer $requestsMailer,
        RegistrationRepository $registrationRepository,
        EducatorRegisterStudentForExperienceRequestRepository $educatorRegisterStudentForExperienceRequestRepository
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
        $this->newCompanyRequestRepository = $newCompanyRequestRepository;
        $this->joinCompanyRequestRepository = $joinCompanyRequestRepository;
        $this->requestRepository = $requestRepository;
        $this->requestsMailer = $requestsMailer;
        $this->registrationRepository = $registrationRepository;
        $this->educatorRegisterStudentForExperienceRequestRepository = $educatorRegisterStudentForExperienceRequestRepository;
    }


    /**
     * @Route("/requests", name="requests", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requests(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $requestsByType = [];

        $requestsThatNeedMyApproval = $this->requestRepository->findBy([
            'needsApprovalBy' => $user,
            'denied' => false,
            'approved' => false,
            'allowApprovalByActivationCode' => false
        ], ['createdAt' => 'DESC']);

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


        // todo you could return a different view per user role as well
        return $this->render('request/index.html.twig', [
            'user' => $user,
            'requestsThatNeedMyApproval' => $requestsThatNeedMyApproval,
            'myCreatedRequests' => $myCreatedRequests,
            'deniedByMeRequests' => $deniedByMeRequests,
            'myDeniedAccessRequests' => $myDeniedAccessRequests,
            'approvedByMeRequests' => $approvedByMeRequests,
            'myApprovedAccessRequests' => $myApprovedAccessRequests,
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
     */
    public function approveRequest(\App\Entity\Request $request, Request $httpRequest) {

        $this->denyAccessUnlessGranted('edit', $request);

        /** @var User $user */
        $user = $this->getUser();

        $this->handleRequestApproval($request, $httpRequest);

        return $this->redirectToRoute('requests');
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
        return $this->redirectToRoute('requests');
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
                Lesson: %s for %s Class
                ", $request->getLesson()->getTitle(), $request->getCreatedBy()->getFullName()));
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
                $request->setApproved(true);
                $this->entityManager->persist($request);

                $studentUsers = $request->getStudentUsers();
                foreach($studentUsers as $studentUser) {

                    // remove any previous registrations if someone is getting registered twice
                    $previousRegistration = $this->registrationRepository->getByUserAndExperience($studentUser, $request->getCompanyExperience());
                    if($previousRegistration) {
                        continue;
                    }

                    $registration = new Registration();
                    $registration->setUser($studentUser);
                    $registration->setExperience($request->getCompanyExperience());
                    $this->entityManager->persist($registration);
                }

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

                $this->addFlash('success', 'Students have been registered in event!');
                $this->entityManager->flush();
                break;
        }
        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }
}
