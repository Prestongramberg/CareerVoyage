<?php

namespace App\Controller;

use App\Entity\CompanyExperience;
use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\ProfessionalReviewCompanyExperienceFeedback;
use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use App\Entity\ProfessionalReviewTeachLessonExperienceFeedback;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\ProfessionalReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\SiteAdminUser;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentReviewMeetProfessionalExperienceFeedback;
use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use App\Entity\StudentReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalReviewStudentToMeetProfessionalFeedback;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Form\EducatorReviewCompanyExperienceFeedbackFormType;
use App\Form\EducatorReviewTeachLessonExperienceFeedbackFormType;
use App\Form\FeedbackType;
use App\Form\Filter\ManageFeedbackFilterType;
use App\Form\Flow\FeedbackFlow;
use App\Form\ManageExperiencesFilterType;
use App\Form\ManageUserFilterType;
use App\Form\ProfessionalReviewTeachLessonExperienceFeedbackFormType;
use App\Form\ProfessionalReviewCompanyExperienceFeedbackFormType;
use App\Form\ProfessionalReviewSchoolExperienceFeedbackFormType;
use App\Form\GenericFeedbackFormType;
use App\Form\StudentReviewCompanyExperienceFeedbackFormType;
use App\Form\StudentReviewMeetProfessionalExperienceFeedbackFormType;
use App\Form\StudentReviewTeachLessonExperienceFeedbackFormType;
use App\Form\StudentReviewSchoolExperienceFeedbackFormType;
use App\Form\ProfessionalReviewStudentToMeetProfessionalFeedbackFormType;
use App\Util\AuthorizationVoter;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class FeedbackController
 *
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
        'sness@ssc.coop',
    ];

    /**
     * @var array
     */
    private $emailsToSendRequestCourseTo = [
        'cpears@apritonadvisors.com',
        'sness@ssc.coop',
    ];


    /**
     * @IsGranted({"ROLE_STUDENT_USER", "ROLE_EDUCATOR_USER", "ROLE_PROFESSIONAL_USER"})
     * @Route("/request-lesson-experience-or-site-visit", name="request_lesson_experience_or_site_visit", options = { "expose" = true })
     * @param  Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestIdeaAction(Request $request)
    {
        /** @var EducatorUser|StudentUser|ProfessionalUser $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder()
                     ->add('message', TextareaType::class, [
                         'label'       => 'Request a topic, experience, or site visit.',
                         'constraints' => [
                             new NotBlank(),
                         ],
                     ])
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->get('message')
                            ->getData();
            $from    = sprintf(
                "Full Name: %s, Email: %s, Username: %s",
                $user->getFullName(),
                !empty($user->getEmail()) ? $user->getEmail() : 'N/A',
                !empty($user->getUsername()) ? $user->getUsername() : 'N/A'
            );

            if (!$user->isProfessional()) {
                foreach (
                    $user->getSchool()
                         ->getSchoolAdministrators() as $schoolAdministrator
                ) {
                    $this->feedbackMailer->requestForLessonIdeaOrSiteVisit($schoolAdministrator, $message, $from);
                }
            }

            foreach ($this->emailsToSendRequestIdeaTo as $emailToSendRequestIdeaTo) {
                $userToSendEmailTo = $this->userRepository->findOneBy([
                    'email' => $emailToSendRequestIdeaTo,
                ]);
                if ($userToSendEmailTo) {
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
     * @param  Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestCourseAction(Request $request)
    {
        /** @var EducatorUser|StudentUser $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder()
                     ->add('message', TextareaType::class, [
                         'label'       => 'Request for a new course to be added to the system.',
                         'constraints' => [
                             new NotBlank(),
                         ],
                     ])
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->get('message')
                            ->getData();

            $from = sprintf(
                "Full Name: %s, Email: %s, Username: %s",
                $user->getFullName(),
                !empty($user->getEmail()) ? $user->getEmail() : 'N/A',
                !empty($user->getUsername()) ? $user->getUsername() : 'N/A'
            );

            foreach ($this->emailsToSendRequestCourseTo as $emailToSendRequestCourseTo) {
                // Chris said he wants an email sent to him when this happens. So here it goes....
                // todo this could probably be refactored or cleaned up somewhere as a constant...
                $userToSendEmailTo = $this->adminUserRepository->findOneBy([
                    'email' => $emailToSendRequestCourseTo,
                ]);
                if ($userToSendEmailTo) {
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
     * @param  Request     $request
     * @param  Experience  $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function experienceFeedbackAction(Request $request, Experience $experience)
    {
        return $this->redirectToRoute('feedback_v2_new', ['uuid' => $experience->getUuid()]);

        /** @var EducatorUser|StudentUser|ProfessionalUser $user */
        $user     = $this->getUser();
        $formType = null;
        $template = null;

        $feedback = $this->feedbackRepository->findOneBy([
            'user'       => $user,
            'experience' => $experience,
        ]);

        $experienceHasFeedback = $feedback ? true : false;

        $studentFeedbackUrl = '';


        // look at the experience object and see which form you should load in
        switch ($experience->getClassName()) {
            case 'CompanyExperience':
            case 'CompanyExperience':
                /** @var CompanyExperience $experience */ if ($user->isStudent()) {
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
                /** @var TeachLessonExperience $experience */ if ($user->isStudent()) {
                $feedback = $feedback = $feedback ? $feedback : new StudentReviewTeachLessonExperienceFeedback();
                $formType = StudentReviewTeachLessonExperienceFeedbackFormType::class;
                $template = 'new_student_review_teach_lesson_experience_feedback.html.twig';
            } elseif ($user->isEducator()) {
                $feedback = $feedback = $feedback ? $feedback : new EducatorReviewTeachLessonExperienceFeedback();
                $formType = EducatorReviewTeachLessonExperienceFeedbackFormType::class;
                $template = 'new_educator_review_teach_lesson_experience_feedback.html.twig';

                $routerContext = $this->router->getContext();
                $scheme        = $routerContext->getScheme();
                $host          = $routerContext->getHost();
                $port          = $routerContext->getHttpPort();

                $studentFeedbackUrl = $scheme.'://'.$host.($port !== 80 ? ':'.$port : '');
                $studentFeedbackUrl .= $this->router->generate('experience_feedback', ['id' => $experience->getId()]);
            } elseif ($user->isProfessional()) {
                $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewTeachLessonExperienceFeedback();
                $formType = ProfessionalReviewTeachLessonExperienceFeedbackFormType::class;
                $template = 'new_professional_review_teach_lesson_experience_feedback.html.twig';
            }

                break;
            case 'StudentToMeetProfessionalExperience':
                /** @var StudentToMeetProfessionalExperience $experience */ if ($user->isProfessional()) {
                $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewStudentToMeetProfessionalFeedback();
                $formType = ProfessionalReviewStudentToMeetProfessionalFeedbackFormType::class;
                $template = 'new_professional_review_student_to_meet_professional_experience_feedback.html.twig';
            } else {
                if ($user->isStudent()) {
                    $feedback = $feedback = $feedback ? $feedback : new StudentReviewMeetProfessionalExperienceFeedback();
                    $formType = StudentReviewMeetProfessionalExperienceFeedbackFormType::class;
                    $template = 'new_student_review_meet_professional_feedback.html.twig';
                } else {
                    $feedback = $feedback = $feedback ? $feedback : new Feedback();
                    $formType = GenericFeedbackFormType::class;
                    $template = 'new_generic_feedback.html.twig';
                }
            }
                break;
            case 'SchoolExperience' :
                if ($user->isStudent()) {
                    $feedback = $feedback = $feedback ? $feedback : new StudentReviewSchoolExperienceFeedback();
                    $formType = StudentReviewSchoolExperienceFeedbackFormType::class;
                    $template = 'new_student_review_school_experience_feedback.html.twig';
                }
                if ($user->isProfessional()) {
                    $feedback = $feedback = $feedback ? $feedback : new ProfessionalReviewSchoolExperienceFeedback();
                    $formType = ProfessionalReviewSchoolExperienceFeedbackFormType::class;
                    $template = 'new_professional_review_school_experience_feedback.html.twig';
                }
                if ($user->isEducator()) {
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

        if (!$feedback || !$formType || !$template) {
            throw new \Exception("Form type, feedback, or template variables not found");
        }

        $form = $this->createForm($formType, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
                    /** @var EducatorReviewCompanyExperienceFeedback $feedback */ /** @var CompanyExperience $experience */ $feedback->setCompanyExperience($experience);
                    $feedback->setEducator($user);
                    break;
                case 'EducatorReviewTeachLessonExperienceFeedback':
                    /** @var EducatorReviewTeachLessonExperienceFeedback $feedback */ /** @var TeachLessonExperience $experience */ $feedback->setTeachLessonExperience($experience);
                    $feedback->setEducator($user);

                    /** @var \App\Entity\Request $request */
                    $request = $experience->getOriginalRequest();

                    if ($actionUrl = $request->getActionUrl()) {
                        $parts = parse_url($actionUrl);
                        parse_str($parts['query'], $query);
                        $lessonId = $query['lesson_id'];
                        $lesson   = $this->lessonRepository->find($lessonId);

                        if ($lesson) {
                            $feedback->setLesson($lesson);
                        }
                    }

                    break;

                case 'ProfessionalReviewStudentToMeetProfessionalFeedback':
                    /** @var ProfessionalReviewStudentToMeetProfessionalFeedback $feedback */ /** @var StudentToMeetProfessionalExperience $experience */ $feedback->setStudentToMeetProfessionalExperience($experience);
                    $feedback->setProfessional($user);
                    $educators = $experience->getOriginalRequest()
                                            ->getStudent()
                                            ->getEducatorUsers();
                    foreach ($educators as $educator) {
                        $this->experienceMailer->notifyTeacherOfProfessionalFeedbackForStudentMeeting($experience, $educator, $feedback);
                    }
                    break;
                case 'ProfessionalReviewTeachLessonExperienceFeedback':
                    /** @var ProfessionalReviewTeachLessonExperienceFeedback $feedback */ /** @var TeachLessonExperience $experience */ $feedback->setTeachLessonExperience($experience);
                    $feedback->setProfessional($user);

                    /** @var \App\Entity\Request $request */
                    $request = $experience->getOriginalRequest();

                    if ($actionUrl = $request->getActionUrl()) {
                        $parts = parse_url($actionUrl);
                        parse_str($parts['query'], $query);
                        $lessonId = $query['lesson_id'];
                        $lesson   = $this->lessonRepository->find($lessonId);

                        if ($lesson) {
                            $feedback->setLesson($lesson);
                        }
                    }

                    break;
                case 'ProfessionalReviewCompanyExperienceFeedback':
                    /** @var ProfessionalReviewCompanyExperienceFeedback $feedback */ /** @var CompanyExperience $experience */ $feedback->setCompanyExperience($experience);
                    $feedback->setProfessional($user);
                    break;
                case 'ProfessionalReviewSchoolExperienceFeedback':
                    /** @var ProfessionalReviewSchoolExperienceFeedback $feedback */ /** @var SchoolExperience $experience */ $feedback->setProfessional($user);
                    $feedback->setSchoolExperience($experience);
                    break;


                case 'StudentReviewCompanyExperienceFeedback':
                    /** @var StudentReviewCompanyExperienceFeedback $feedback */ /** @var CompanyExperience $experience */ $feedback->setStudent($user);
                    $feedback->setCompanyExperience($experience);
                    break;
                case 'StudentReviewTeachLessonExperienceFeedback':
                    /** @var StudentReviewTeachLessonExperienceFeedback $feedback */ /** @var TeachLessonExperience $experience */ $feedback->setTeachLessonExperience($experience);
                    $feedback->setStudent($user);

                    /** @var \App\Entity\Request $request */
                    $request = $experience->getOriginalRequest();

                    if ($actionUrl = $request->getActionUrl()) {
                        $parts = parse_url($actionUrl);
                        parse_str($parts['query'], $query);
                        $lessonId = $query['lesson_id'];
                        $lesson   = $this->lessonRepository->find($lessonId);

                        if ($lesson) {
                            $feedback->setLesson($lesson);
                        }
                    }

                    break;
                case 'StudentReviewMeetProfessionalExperienceFeedback':
                    /** @var StudentReviewMeetProfessionalExperienceFeedback $feedback */ /** @var StudentToMeetProfessionalExperience $experience */ $feedback->setStudentToMeetProfessionalExperience($experience);
                    $feedback->setStudent($user);
                    break;
                case 'StudentReviewSchoolExperienceFeedback':
                    /** @var StudentReviewSchoolExperienceFeedback $feedback */ /** @var SchoolExperience $experience */ $feedback->setStudent($user);
                    $feedback->setSchoolExperience($experience);
                    break;


                default:
                    /** @var Feedback $feedback */ /** @var TeachLessonExperience $experience */ // do nothing
                    break;
            }

            $this->entityManager->persist($feedback);
            $this->entityManager->flush();

            $this->addFlash('success', 'Feedback successfully submitted.');

            return $this->redirectToRoute('dashboard', ["tab" => "user-reviews"]);
        }

        return $this->render("feedback/{$template}", [
            'user'                  => $user,
            'form'                  => $form->createView(),
            'feedback'              => $feedback,
            'experience'            => $experience,
            'experienceHasFeedback' => $experienceHasFeedback,
            'studentFeedbackUrl'    => $studentFeedbackUrl,
        ]);
    }

    /**
     * @Route("/view-all", name="feedback_view_all", options = { "expose" = true })
     * @param  Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function feedbackViewAllAction(Request $request)
    {
        // TODO We could use a form filter type here and put the query in here instead.

        $loggedInUser = $user = $this->getUser();
        // TODO Wire up query params to help filter the feedback/experience data
        $schoolId     = $request->query->get('schoolId');
        $companyId    = $request->query->get('companyId');
        $experienceId = $request->query->get('experienceId');
        $school             = null;
        $company            = null;
        $experience         = null;
        $actionUrlParams    = [];
        $authorizationVoter = new AuthorizationVoter();

        if (!$schoolId && !$companyId) {
            if ($user instanceof SchoolAdministrator) {
                $school = $user->getSchools()
                               ->first();

                if ($school) {
                    return $this->redirectToRoute('feedback_view_all', ['schoolId' => $school->getId()]);
                }

                throw new AccessDeniedException();
            } elseif ($user instanceof EducatorUser) {
                $school = $user->getSchool();

                if ($school) {
                    return $this->redirectToRoute('feedback_view_all', ['schoolId' => $school->getId()]);
                }

                throw new AccessDeniedException();
            } elseif ($user instanceof ProfessionalUser) {
                $company = $user->getOwnedCompany();

                if ($company) {
                    return $this->redirectToRoute('feedback_view_all', ['companyId' => $company->getId()]);
                }
            }

            throw new AccessDeniedException();
        }

        $experiences = [];
        if ($experienceId) {
            $experience = $this->experienceRepository->find($experienceId);

            if (!$experience) {
                throw new AccessDeniedException();
            }

            $actionUrlParams['experienceId'] = $experienceId;
            $experiences[] = $experience;
        }

        // SCHOOL ADMINISTRATORS
        if ($loggedInUser instanceof SchoolAdministrator && !$experienceId && !$schoolId) {
            $schoolIds = [];
            foreach ($loggedInUser->getSchools() as $schoolObject) {
                $schoolIds[] = $schoolObject->getId();
            }

            $filterBuilder = $this->schoolExperienceRepository->createQueryBuilder('e')
                                                            ->innerJoin('e.school', 'school')
                                                            ->andWhere('school.id IN (:schoolIds)')
                                                            ->setParameter('schoolIds', $schoolIds);
        }

        // EDUCATORS
        if ($loggedInUser instanceof EducatorUser && !$experienceId && !$schoolId) {
            $filterBuilder = $this->schoolExperienceRepository->createQueryBuilder('e')
                                                            ->innerJoin('e.school', 'school')
                                                            ->leftJoin('e.schoolContact', 'schoolContact')
                                                            ->leftJoin('e.creator', 'creator')
                                                            ->andWhere(
                                                                'schoolContact.id = :schoolContactId or creator.id = :creatorId'
                                                            )
                                                            ->setParameter('schoolContactId', $loggedInUser->getId())
                                                            ->setParameter('creatorId', $loggedInUser->getId());
        }


        if ($schoolId) {
            $actionUrlParams['schoolId'] = $schoolId;
            $school = $this->schoolRepository->find($schoolId);

            if (!$school || !$authorizationVoter->canCreateExperiencesForSchool($user, $school)) {
                throw new AccessDeniedException();
            }

            $filterBuilder = $this->schoolExperienceRepository->createQueryBuilder('e')
                                                            ->innerJoin('e.school', 'school')
                                                            ->andWhere('school.id = :schoolId')
                                                            ->setParameter('schoolId', $schoolId);
        }

        if ($companyId) {
            $actionUrlParams['companyId'] = $companyId;
            $company = $this->companyRepository->find($companyId);

            if (!$company || !$authorizationVoter->canManageExperiencesForCompany($user, $company)) {
                throw new AccessDeniedException();
            }

            $filterBuilder = $this->companyExperienceRepository->createQueryBuilder('e')
                                                             ->innerJoin('e.company', 'company')
                                                             ->andWhere('company.id = :companyId')
                                                             ->setParameter('companyId', $companyId);
        }


        /* ORDER BY UPCOMING EVENTS FIRST */
        $filterBuilder->addSelect("(CASE WHEN e.startDateAndTime >= CURRENT_DATE() THEN 1 ELSE 0 END) AS HIDDEN ORDER_BY_1 ");
        /* ORDER BY EVENTS CLOSEST TO THE CURRENT DATE NEXT */
        $filterBuilder->addSelect("abs ( DATE_DIFF ( e.startDateAndTime, CURRENT_DATE() ) ) AS HIDDEN ORDER_BY_2 ");
        /* ORDER BY EVENTS THAT HAVE A START DATE IN THE PAST BUT ARE STILL GOING ON (LOT OF PEOPLE ARE DOING THIS FOR REPEATING EVENTS) */
        $filterBuilder->addSelect("(CASE WHEN e.startDateAndTime < CURRENT_DATE() AND e.endDateAndTime > CURRENT_DATE() THEN 1 ELSE 0 END) AS HIDDEN ORDER_BY_3");

        $filterBuilder->addOrderBy('ORDER_BY_1', 'DESC');
        $filterBuilder->addOrderBy('ORDER_BY_2', 'ASC');
        $filterBuilder->addOrderBy('ORDER_BY_3', 'DESC');

        $actionUrl = $this->generateUrl('feedback_view_all', $actionUrlParams);
        $form = $this->createForm(ManageFeedbackFilterType::class, null, [
            'action' => $actionUrl,
            'method' => 'GET',
            'filterableExperiences' => []
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();
        $experiences = $filterQuery->getResult();

        $pagination = $this->paginator->paginate(
            $filterBuilder->getQuery(),
            /* query NOT result */ $request->query->getInt('page', 1),
            /*page number*/ $request->query->getInt('limit', 10)
        );

        return $this->render("feedback/view_all.html.twig", [
            'user'        => $loggedInUser,
            'experiences' => $experiences,
            'clearFormUrl' => $this->generateUrl('feedback_view_all', $actionUrlParams),
            'showFilters' => $request->query->has('showFilters'),
            'form' => $form->createView(),
            'school' => $school,
            'company' => $company,
            'experience' => $experience,
            'pagination' => $pagination
        ]);
    }

}
