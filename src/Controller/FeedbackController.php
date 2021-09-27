<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\ProfessionalReviewCompanyExperienceFeedback;
use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use App\Entity\ProfessionalReviewTeachLessonExperienceFeedback;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalReviewMeetStudentExperienceFeedback;
use App\Entity\ProfessionalReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolExperience;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentReviewMeetProfessionalExperienceFeedback;
use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use App\Entity\StudentReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalReviewStudentToMeetProfessionalFeedback;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Form\EducatorReviewCompanyExperienceFeedbackFormType;
use App\Form\EducatorReviewTeachLessonExperienceFeedbackFormType;
use App\Form\ProfessionalReviewTeachLessonExperienceFeedbackFormType;
use App\Form\ProfessionalReviewCompanyExperienceFeedbackFormType;
use App\Form\ProfessionalReviewSchoolExperienceFeedbackFormType;
use App\Form\FeedbackFormType;
use App\Form\GenericFeedbackFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RegionalCoordinatorFormType;
use App\Form\StateCoordinatorFormType;
use App\Form\StudentReviewCompanyExperienceFeedbackFormType;
use App\Form\StudentReviewMeetProfessionalExperienceFeedbackFormType;
use App\Form\StudentReviewTeachLessonExperienceFeedbackFormType;
use App\Form\StudentReviewSchoolExperienceFeedbackFormType;
use App\Form\ProfessionalReviewStudentToMeetProfessionalFeedbackFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class FeedbackController
 * @package App\Controller
 * @Route("/dashboard/feedback")
 */
class FeedbackController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @var array
     */
    private $emailsToSendRequestIdeaTo = [
        'cpears@apritonadvisors.com',
        'sness@ssc.coop'
    ];

    /**
     * @var array
     */
    private $emailsToSendRequestCourseTo = [
        'cpears@apritonadvisors.com',
        'sness@ssc.coop'
    ];

    /**
     * @IsGranted({"ROLE_STUDENT_USER", "ROLE_EDUCATOR_USER", "ROLE_PROFESSIONAL_USER"})
     * @Route("/request-lesson-experience-or-site-visit", name="request_lesson_experience_or_site_visit", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestIdeaAction(Request $request) {

        /** @var EducatorUser|StudentUser|ProfessionalUser $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('message', TextareaType::class, ['label' => 'Request a topic, experience, or site visit.',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $message = $form->get('message')->getData();
            $from = sprintf("Full Name: %s, Email: %s, Username: %s",
                $user->getFullName(),
                !empty($user->getEmail()) ? $user->getEmail() : 'N/A',
                !empty($user->getUsername()) ? $user->getUsername() : 'N/A'
            );

            if (!$user->isProfessional()) {
                foreach($user->getSchool()->getSchoolAdministrators() as $schoolAdministrator) {
                    $this->feedbackMailer->requestForLessonIdeaOrSiteVisit($schoolAdministrator, $message, $from);
                }
            }

            foreach($this->emailsToSendRequestIdeaTo as $emailToSendRequestIdeaTo) {
                $userToSendEmailTo = $this->userRepository->findOneBy([
                    'email' => $emailToSendRequestIdeaTo
                ]);
                if($userToSendEmailTo) {
                    $this->feedbackMailer->requestForLessonIdeaOrSiteVisit($userToSendEmailTo, $message, $from);
                }
            }


            $this->addFlash('success', 'Feedback successfully submitted.');
            return $this->redirectToRoute('request_lesson_experience_or_site_visit');
        }

        return $this->render('feedback/request_lesson_experience_or_site_visit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/request-to-add-new-course-to-system", name="request_to_add_course", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestCourseAction(Request $request) {

        /** @var EducatorUser|StudentUser $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('message', TextareaType::class, ['label' => 'Request for a new course to be added to the system.',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $message = $form->get('message')->getData();

            $from = sprintf("Full Name: %s, Email: %s, Username: %s",
                $user->getFullName(),
                !empty($user->getEmail()) ? $user->getEmail() : 'N/A',
                !empty($user->getUsername()) ? $user->getUsername() : 'N/A'
            );

            foreach($this->emailsToSendRequestCourseTo as $emailToSendRequestCourseTo) {
                // Chris said he wants an email sent to him when this happens. So here it goes....
                // todo this could probably be refactored or cleaned up somewhere as a constant...
                $userToSendEmailTo = $this->adminUserRepository->findOneBy([
                    'email' => $emailToSendRequestCourseTo
                ]);
                if($userToSendEmailTo) {
                    $this->feedbackMailer->requestForNewCourseToBeAddedToSystem($userToSendEmailTo, $message, $from);
                }
            }


            $this->addFlash('success', 'Feedback successfully submitted.');
            return $this->redirectToRoute('request_to_add_course');
        }

        return $this->render('feedback/request_course.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted({"ROLE_STUDENT_USER", "ROLE_EDUCATOR_USER", "ROLE_PROFESSIONAL_USER"})
     * @Route("/experiences/{id}", name="experience_feedback", options = { "expose" = true })
     * @param Request $request
     * @param Experience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function experienceFeedbackAction(Request $request, Experience $experience) {

        /** @var EducatorUser|StudentUser|ProfessionalUser $user */
        $user = $this->getUser();
        $formType = null;
        $template = null;

        $feedback = $this->feedbackRepository->findOneBy([
           'user' => $user,
           'experience' => $experience
        ]);

        $experienceHasFeedback = $feedback ? true : false;

        $studentFeedbackUrl = '';


        // look at the experience object and see which form you should load in
        switch ($experience->getClassName()) {
            case 'CompanyExperience':case 'CompanyExperience':
                /** @var CompanyExperience $experience */
                if($user->isStudent()) {
                    $feedback = $feedback ? $feedback : new StudentReviewCompanyExperienceFeedback();
                    $formType = StudentReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'new_student_review_company_experience_feedback.html.twig';
                } elseif ($user->isEducator()) {
                    $feedback = $feedback = $feedback ? $feedback : new EducatorReviewCompanyExperienceFeedback();
                    $formType = EducatorReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'new_educator_review_company_experience_feedback.html.twig';
                } elseif ($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewCompanyExperienceFeedback();
                    $formType = ProfessionalReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'new_professional_review_company_experience_feedback.html.twig';
                }
                break;
            case 'TeachLessonExperience':
                /** @var TeachLessonExperience $experience */
                if($user->isStudent()) {
                    $feedback = $feedback = $feedback ? $feedback : new StudentReviewTeachLessonExperienceFeedback();
                    $formType = StudentReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'new_student_review_teach_lesson_experience_feedback.html.twig';
                } elseif ($user->isEducator()) {
                    $feedback = $feedback = $feedback ? $feedback : new EducatorReviewTeachLessonExperienceFeedback();
                    $formType = EducatorReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'new_educator_review_teach_lesson_experience_feedback.html.twig';

                    $routerContext = $this->router->getContext();
                    $scheme = $routerContext->getScheme();
                    $host = $routerContext->getHost();
                    $port = $routerContext->getHttpPort();

                    $studentFeedbackUrl = $scheme . '://' . $host . ($port !== 80 ? ':'. $port : '');
                    $studentFeedbackUrl .= $this->router->generate('experience_feedback', ['id' => $experience->getId()]);
                } elseif ($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewTeachLessonExperienceFeedback();
                    $formType = ProfessionalReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'new_professional_review_teach_lesson_experience_feedback.html.twig';
                }

                break;
            case 'StudentToMeetProfessionalExperience':
                /** @var StudentToMeetProfessionalExperience $experience */
                if($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewStudentToMeetProfessionalFeedback();
                    $formType = ProfessionalReviewStudentToMeetProfessionalFeedbackFormType::class;
                    $template = 'new_professional_review_student_to_meet_professional_experience_feedback.html.twig';
                } else if($user->isStudent()) {
                    $feedback = $feedback = $feedback ? $feedback : new StudentReviewMeetProfessionalExperienceFeedback();
                    $formType = StudentReviewMeetProfessionalExperienceFeedbackFormType::class;
                    $template = 'new_student_review_meet_professional_feedback.html.twig';
                } else {
                    $feedback = $feedback = $feedback ? $feedback : new Feedback();
                    $formType = GenericFeedbackFormType::class;
                    $template = 'new_generic_feedback.html.twig';
                }
                break;
            case 'SchoolExperience' :
                if($user->isStudent()) {
                    $feedback = $feedback = $feedback ? $feedback : new StudentReviewSchoolExperienceFeedback();
                    $formType = StudentReviewSchoolExperienceFeedbackFormType::class;
                    $template = 'new_student_review_school_experience_feedback.html.twig';
                }
                if($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewSchoolExperienceFeedback();
                    $formType = ProfessionalReviewSchoolExperienceFeedbackFormType::class;
                    $template = 'new_professional_review_school_experience_feedback.html.twig';
                }
                if($user->isEducator()) {
                    $feedback = $feedback = $feedback ? $feedback : new Feedback();
                    $formType = GenericFeedbackFormType::class;
                    $template = 'new_generic_feedback.html.twig';
                }
                break;
            default:
                $feedback = $feedback = $feedback ? $feedback : new Feedback();
                $formType = GenericFeedbackFormType::class;
                $template = 'new_generic_feedback.html.twig';
                break;
        }

        if(!$feedback || !$formType || !$template) {
            throw new \Exception("Form type, feedback, or template variables not found");
        }

        $form = $this->createForm($formType, $feedback);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var Feedback $feedback */
            $feedback = $form->getData();

            //todo we might not need the user and experience on each subclass of the feedback object
            $feedback->setUser($user);
            $feedback->setExperience($experience);

            $name = $feedback->getClassName();

            // echo $name;
            // die();
            switch ($feedback->getClassName()) {
                case 'EducatorReviewCompanyExperienceFeedback':
                    /** @var EducatorReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setCompanyExperience($experience);
                    $feedback->setEducator($user);
                    break;
                case 'EducatorReviewTeachLessonExperienceFeedback':
                    /** @var EducatorReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setEducator($user);

                    /** @var \App\Entity\Request $request */
                    $request = $experience->getOriginalRequest();

                    if($actionUrl = $request->getActionUrl()) {
                        $parts = parse_url($actionUrl);
                        parse_str($parts['query'], $query);
                        $lessonId = $query['lesson_id'];
                        $lesson = $this->lessonRepository->find($lessonId);

                        if($lesson) {
                            $feedback->setLesson($lesson);
                        }

                    }

                    break;

                case 'ProfessionalReviewStudentToMeetProfessionalFeedback':
                    /** @var ProfessionalReviewStudentToMeetProfessionalFeedback $feedback */
                    /** @var StudentToMeetProfessionalExperience $experience */
                    $feedback->setStudentToMeetProfessionalExperience($experience);
                    $feedback->setProfessional($user);
                    $educators = $experience->getOriginalRequest()->getStudent()->getEducatorUsers();
                    foreach ($educators as $educator) {
                        $this->experienceMailer->notifyTeacherOfProfessionalFeedbackForStudentMeeting($experience, $educator, $feedback);
                    }
                    break;
                case 'ProfessionalReviewTeachLessonExperienceFeedback':                 
                    /** @var ProfessionalReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setProfessional($user);

                    /** @var \App\Entity\Request $request */
                    $request = $experience->getOriginalRequest();

                    if($actionUrl = $request->getActionUrl()) {
                        $parts = parse_url($actionUrl);
                        parse_str($parts['query'], $query);
                        $lessonId = $query['lesson_id'];
                        $lesson = $this->lessonRepository->find($lessonId);

                        if($lesson) {
                            $feedback->setLesson($lesson);
                        }

                    }

                    break;
                case 'ProfessionalReviewCompanyExperienceFeedback':
                    /** @var ProfessionalReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setCompanyExperience($experience);
                    $feedback->setProfessional($user);
                    break;
                case 'ProfessionalReviewSchoolExperienceFeedback':
                    /** @var ProfessionalReviewSchoolExperienceFeedback $feedback */
                    /** @var SchoolExperience $experience */
                    $feedback->setProfessional($user);
                    $feedback->setSchoolExperience($experience);
                    break;

                
                case 'StudentReviewCompanyExperienceFeedback':
                    /** @var StudentReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setStudent($user);
                    $feedback->setCompanyExperience($experience);
                    break;
                case 'StudentReviewTeachLessonExperienceFeedback':
                    /** @var StudentReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setStudent($user);

                    /** @var \App\Entity\Request $request */
                    $request = $experience->getOriginalRequest();

                    if($actionUrl = $request->getActionUrl()) {
                        $parts = parse_url($actionUrl);
                        parse_str($parts['query'], $query);
                        $lessonId = $query['lesson_id'];
                        $lesson = $this->lessonRepository->find($lessonId);

                        if($lesson) {
                            $feedback->setLesson($lesson);
                        }

                    }

                    break;
                case 'StudentReviewMeetProfessionalExperienceFeedback':
                    /** @var StudentReviewMeetProfessionalExperienceFeedback $feedback */
                    /** @var StudentToMeetProfessionalExperience $experience */
                    $feedback->setStudentToMeetProfessionalExperience($experience);
                    $feedback->setStudent($user);
                    break;
                case 'StudentReviewSchoolExperienceFeedback':
                    /** @var StudentReviewSchoolExperienceFeedback $feedback */
                    /** @var SchoolExperience $experience */
                    $feedback->setStudent($user);
                    $feedback->setSchoolExperience($experience);
                    break;
                    

                default:
                    /** @var Feedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    // do nothing
                    break;
            }

            $this->entityManager->persist($feedback);
            $this->entityManager->flush();

            $this->addFlash('success', 'Feedback successfully submitted.');
            return $this->redirectToRoute('dashboard', array("tab" => "user-reviews"));
        }

        return $this->render("feedback/{$template}", [
            'user' => $user,
            'form' => $form->createView(),
            'feedback' => $feedback,
            'experience' => $experience,
            'experienceHasFeedback' => $experienceHasFeedback,
            'studentFeedbackUrl' => $studentFeedbackUrl
        ]);
    }



    /* ------------------------------------------------------------ */
    /* This function below is for ajax deleting of requests. It is  */
    /* basically the same as experienceFeedbackAction in the submit */
    /* of the form.
    /* ----------------------------------------------------------- */

    /**
     * @Route("/experiences/{id}/delete", name="feedback_delete", options ={ "expose" = true})
     * @param Request $request
     * @param Experience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function feedbackDeleteAction(Request $request, Experience $experience) {

        $user = $this->getUser();

        $formType = null;
        $template = null;

        $feedback = $this->feedbackRepository->findOneBy([
           'user' => $user,
           'experience' => $experience
        ]);

        $experienceHasFeedback = $feedback ? true : false;

        // look at the experience object and see which form you should load in
        switch ($experience->getClassName()) {
            case 'CompanyExperience':
                /** @var CompanyExperience $experience */
                if($user->isStudent()) {
                    $feedback = $feedback ? $feedback : new StudentReviewCompanyExperienceFeedback();
                    $formType = StudentReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'delete_forms/new_student_review_company_experience_feedback.html.twig';
                } elseif ($user->isEducator()) {
                    $feedback = $feedback = $feedback ? $feedback : new EducatorReviewCompanyExperienceFeedback();
                    $formType = EducatorReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'delete_forms/new_educator_review_company_experience_feedback.html.twig';
                } elseif ($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewCompanyExperienceFeedback();
                    $formType = ProfessionalReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'delete_forms/new_professional_review_company_experience_feedback.html.twig';
                }
                break;
            case 'TeachLessonExperience':
                /** @var TeachLessonExperience $experience */
                if($user->isStudent()) {
                    $feedback = $feedback = $feedback ? $feedback : new StudentReviewTeachLessonExperienceFeedback();
                    $formType = StudentReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'delete_forms/new_student_review_teach_lesson_experience_feedback.html.twig';
                } elseif ($user->isEducator()) {
                    $feedback = $feedback = $feedback ? $feedback : new EducatorReviewTeachLessonExperienceFeedback();
                    $formType = EducatorReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'delete_forms/new_educator_review_teach_lesson_experience_feedback.html.twig';
                } elseif ($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewTeachLessonExperienceFeedback();
                    $formType = ProfessionalReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'delete_forms/new_professional_review_teach_lesson_experience_feedback.html.twig';
                }

                break;
            case 'StudentToMeetProfessionalExperience':
                /** @var StudentToMeetProfessionalExperience $experience */
                if($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewStudentToMeetProfessionalFeedback();
                    $formType = ProfessionalReviewStudentToMeetProfessionalFeedbackFormType::class;
                    $template = 'delete_forms/new_professional_review_student_to_meet_professional_experience_feedback.html.twig';
                } else if($user->isStudent()) {
                    $feedback = $feedback = $feedback ? $feedback : new StudentReviewMeetProfessionalExperienceFeedback();
                    $formType = StudentReviewMeetProfessionalExperienceFeedbackFormType::class;
                    $template = 'delete_forms/new_student_review_meet_professional_feedback.html.twig';
                } else {
                    $feedback = $feedback = $feedback ? $feedback : new Feedback();
                    $formType = GenericFeedbackFormType::class;
                    $template = 'delete_forms/new_generic_feedback.html.twig';
                }
                break;
            default:
                $feedback = $feedback = $feedback ? $feedback : new Feedback();
                $formType = GenericFeedbackFormType::class;
                $template = 'delete_forms/new_generic_feedback.html.twig';
                break;
        }

        $form = $this->createForm($formType, $feedback);
        $form->handleRequest($request);

        
        if($form->isSubmitted() && $form->isValid()) {

            /** @var Feedback $feedback */
            $feedback = $form->getData();

            //todo we might not need the user and experience on each subclass of the feedback object
            $feedback->setUser($user);
            $feedback->setExperience($experience);

            $name = $feedback->getClassName();

            // echo $feedback->getClassName();
            //     die();
            switch ($feedback->getClassName()) {


                case 'EducatorReviewCompanyExperienceFeedback':
                    /** @var EducatorReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setCompanyExperience($experience);
                    $feedback->setEducator($user);

                    break;
                case 'EducatorReviewTeachLessonExperienceFeedback':
                    /** @var EducatorReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setEducator($user);
                    $feedback->setLesson($experience->getOriginalRequest()->getLesson());
                    break;
                
                case 'ProfessionalReviewStudentToMeetProfessionalFeedback':
                    /** @var ProfessionalReviewStudentToMeetProfessionalFeedback $feedback */
                    /** @var StudentToMeetProfessionalExperience $experience */
                    $feedback->setStudentToMeetProfessionalExperience($experience);
                    $feedback->setProfessional($user);
                    break;
                case 'ProfessionalReviewTeachLessonExperienceFeedback':
                    /** @var ProfessionalReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setProfessional($user);
                    $feedback->setLesson($experience->getOriginalRequest()->getLesson());
                    break;
                case 'ProfessionalReviewCompanyExperienceFeedback':
                    /** @var ProfessionalReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setCompanyExperience($experience);
                    $feedback->setProfessional($user);
                    break;
                    
                case 'StudentReviewMeetProfessionalExperienceFeedback':
                    /** @var StudentReviewMeetProfessionalExperienceFeedback $feedback */
                    /** @var StudentToMeetProfessionalExperience $experience */
                    $feedback->setStudentToMeetProfessionalExperience($experience);
                    $feedback->setStudent($user);
                    break;
                case 'StudentReviewCompanyExperienceFeedback':
                    /** @var StudentReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setStudent($user);
                    $feedback->setCompanyExperience($experience);
                    break;
                case 'StudentReviewTeachLessonExperienceFeedback':
                    /** @var StudentReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setStudent($user);
                    $feedback->setLesson($experience->getOriginalRequest()->getLesson());
                    break;
                
                default:
                    /** @var Feedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    // do nothing
                    break;
            }

            $this->entityManager->persist($feedback);
            $this->entityManager->flush();

            return $this->render('feedback/delete_forms/success.html.twig');
        }
        
        
        return $this->render("feedback/{$template}", [
            'user' => $user,
            'form' => $form->createView(),
            'feedback' => $feedback,
            'experience' => $experience,
            'experienceHasFeedback' => $experienceHasFeedback
        ]);
    }


    /**
     * @Route("/{id}/view", name="feedback_view", options = { "expose" = true })
     * @param Request $request
     * @param Feedback $feedback
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function feedbackViewAction(Request $request, Feedback $feedback) {

        $user = $this->getUser();
        $formType = null;
        $template = null;
        switch ($feedback->getClassName()) {
            case 'EducatorReviewCompanyExperienceFeedback':
                /** @var EducatorReviewCompanyExperienceFeedback $feedback */
                /** @var CompanyExperience $experience */
                $formType = EducatorReviewCompanyExperienceFeedbackFormType::class;
                $template = 'view_educator_review_company_experience_feedback.html.twig';
                break;
            case 'EducatorReviewTeachLessonExperienceFeedback':
                /** @var EducatorReviewTeachLessonExperienceFeedback $feedback */
                /** @var TeachLessonExperience $experience */
                $formType = EducatorReviewTeachLessonExperienceFeedbackFormType::class;
                $template = 'view_educator_review_teach_lesson_experience_feedback.html.twig';
                break;
            case 'StudentReviewCompanyExperienceFeedback':
                /** @var StudentReviewCompanyExperienceFeedback $feedback */
                /** @var CompanyExperience $experience */
                $formType = StudentReviewCompanyExperienceFeedbackFormType::class;
                $template = 'view_student_review_company_experience_feedback.html.twig';
                break;
            case 'StudentReviewTeachLessonExperienceFeedback':
                /** @var StudentReviewTeachLessonExperienceFeedback $feedback */
                /** @var TeachLessonExperience $experience */
                $formType = StudentReviewTeachLessonExperienceFeedbackFormType::class;
                $template = 'view_student_review_teach_lesson_experience_feedback.html.twig';
                break;
            case 'ProfessionalReviewStudentToMeetProfessionalFeedback':
                /** @var ProfessionalReviewStudentToMeetProfessionalFeedback $feedback */
                /** @var StudentToMeetProfessionalExperience $experience */
                $formType = ProfessionalReviewStudentToMeetProfessionalFeedbackFormType::class;
                $template = 'view_professional_review_student_to_meet_professional_experience_feedback.html.twig';
                break;
            default:
                $formType = GenericFeedbackFormType::class;
                $template = 'view_generic_feedback.html.twig';
                break;
        }

        if(!$feedback || !$formType || !$template) {
            throw new \Exception("Form type, feedback, or template variables not found");
        }

        return $this->render("feedback/{$template}", [
            'user' => $user,
            'feedback' => $feedback,
        ]);
    }

    /**
     * @IsGranted({"ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER"})
     *
     * @Route("/view-all", name="feedback_view_all", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function feedbackViewAllAction(Request $request) {

        $user = $this->getUser();

        return $this->render("feedback/view_all.html.twig", [
            'user' => $user
        ]);
    }
}
