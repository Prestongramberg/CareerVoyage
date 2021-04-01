<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\EmailLog;
use App\Entity\Feedback;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RolesWillingToFulfill;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EventTypeFormType;
use App\Form\Filter\Report\Dashboard\FeedbackFilterType;
use App\Form\Filter\Report\Dashboard\RegionFilterType;
use App\Form\Filter\Report\Dashboard\SchoolFilterType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RolesFormType;
use App\Form\SiteAdminFormType;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use App\Model\Report\Dashboard\Feedback\BarChart\ExperienceRating;
use App\Model\Report\Dashboard\Feedback\BarChart\StudentInterestInWorkingForCompany;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\RolesWillingToFulfillRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FeedbackGenerator;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
 * Class ReportController
 *
 * @Route("/dashboard/reports")
 */
class ReportController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER", "ROLE_PROFESSIONAL_USER"})
     * @Route("/download/{reportName}", name="report_index")
     *
     * @param Request $request
     *
     * @param null    $reportName
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, $reportName = null)
    {
        /** @var User $user */
        $user = $this->getUser();

        $reports = [

            'EXPERIENCE_FEEDBACK_RESULTS' => [
                'title' => 'Experience Feedback Results',
                'name' => 'EXPERIENCE_FEEDBACK_RESULTS',
                'roles' => function () use ($user) {

                    if ($user->isSchoolAdministrator() || $user->isEducator()) {
                        return true;
                    }

                    if ($user->isProfessional() && $user->getOwnedCompany()) {
                        return true;
                    }

                    $employeeContact = $this->companyExperienceRepository->findOneBy(
                        [
                            'employeeContact' => $user,
                        ]
                    );

                    if ($employeeContact) {
                        return true;
                    }


                },
                'query' => function () use ($user) {

                    $experiences = [];
                    if ($user->isSchoolAdministrator()) {
                        /** @var SchoolAdministrator $user */
                        foreach ($user->getSchools() as $school) {
                            $experiences = array_merge($experiences, $this->experienceRepository->getEventsBySchool($school));
                        }
                    }

                    if ($user->isEducator()) {
                        /** @var EducatorUser $user */
                        $experiences = array_merge($experiences, $this->experienceRepository->getEventsBySchool($user->getSchool()));
                    }

                    if ($user->isProfessional()) {
                        /** @var ProfessionalUser $user */

                        if ($company = $user->getOwnedCompany()) {
                            $experiences = array_merge(
                                $experiences, $this->companyExperienceRepository->findBy(
                                [
                                    'company' => $company,
                                ]
                            )
                            );
                        }

                        $experiences = array_merge(
                            $experiences, $this->companyExperienceRepository->findBy(
                            [
                                'company' => $company,
                            ]
                        ), $this->companyExperienceRepository->findBy(
                            [
                                'employeeContact' => $user,
                            ]
                        )
                        );

                    }

                    $experienceResults = $this->experienceRepository->findAll(['start_date_and_time' => 'desc']);

                    $feedbackGenerator = new FeedbackGenerator($experienceResults, $user, $this->twig);

                    $data = [];
                    foreach ($feedbackGenerator as $experience) {
                        foreach ($experience->getFeedback() as $feedback) {
                            $data[] = [
                                'Experience Id' => $experience->getId(),
                                'Experience Title' => $experience->getTitle(),
                                'Feedback Rating' => $feedback->getRating(),
                                'Insight' => $feedback->getProvidedCareerInsight(),
                                'Enjoyable' => $feedback->getWasEnjoyableAndEngaging(),
                                'Learned' => $feedback->getLearnSomethingNew(),
                                'Recommendation' => $feedback->getLikelihoodToRecommendToFriend(),
                            ];
                        }
                    }

                    return $data;

                },
            ],

            'EXPERIENCE_DATA_BREAKDOWN' => [
                'title' => 'Experience Data Breakdown',
                'name' => 'EXPERIENCE_DATA_BREAKDOWN',
                'roles' => function () use ($user) {

                    if ($user->isSchoolAdministrator() || $user->isEducator()) {
                        return true;
                    }

                    if ($user->isProfessional() && $user->getOwnedCompany()) {
                        return true;
                    }

                    $employeeContact = $this->companyExperienceRepository->findOneBy(
                        [
                            'employeeContact' => $user,
                        ]
                    );

                    if ($employeeContact) {
                        return true;
                    }


                },
                'query' => function () use ($user) {

                    $experiences = [];
                    if ($user->isSchoolAdministrator()) {
                        /** @var SchoolAdministrator $user */
                        foreach ($user->getSchools() as $school) {
                            $experiences = array_merge($experiences, $this->experienceRepository->getEventsBySchool($school));
                        }
                    }

                    if ($user->isEducator()) {
                        /** @var EducatorUser $user */
                        $experiences = array_merge($experiences, $this->experienceRepository->getEventsBySchool($user->getSchool()));
                    }

                    if ($user->isProfessional()) {
                        /** @var ProfessionalUser $user */

                        if ($company = $user->getOwnedCompany()) {
                            $experiences = array_merge(
                                $experiences, $this->companyExperienceRepository->findBy(
                                [
                                    'company' => $company,
                                ]
                            )
                            );
                        }

                        $experiences = array_merge(
                            $experiences, $this->companyExperienceRepository->findBy(
                            [
                                'company' => $company,
                            ]
                        ), $this->companyExperienceRepository->findBy(
                            [
                                'employeeContact' => $user,
                            ]
                        )
                        );

                    }

                    $experienceResults = $this->experienceRepository->findAll(['start_date_and_time' => 'desc']);

                    $feedbackGenerator = new FeedbackGenerator($experienceResults, $user, $this->twig);

                    $data = [];
                    foreach ($feedbackGenerator as $experience) {
                        $data[] = [
                            'Experience Id' => $experience->getId(),
                            'Experience Title' => $experience->getTitle(),
                            'Learned Something' => $feedbackGenerator->cumulativeLearned(),
                            'Provided Career Insight' => $feedbackGenerator->cumulativeInsight(),
                            'Enjoyable And Engaging' => $feedbackGenerator->cumulativeEnjoyable(),
                            'Average Rating' => $feedbackGenerator->cumulativeRating(),
                            'Total Responses' => $feedbackGenerator->totalFeedback(),
                            'NPM Score' => $feedbackGenerator->npmScore(),
                        ];
                    }

                    return $data;

                },
            ],

            'LESSONS_I_WANT_TAUGHT' => [
                'title' => 'Lessons Educators and School Administrators Want Taught',
                'name' => 'LESSONS_I_WANT_TAUGHT',
                'roles' => [
                    'ROLE_ADMIN_USER',
                    'ROLE_SITE_ADMIN_USER',
                    'ROLE_REGIONAL_COORDINATOR_USER',
                ],
                'query' => 'select l.id as "Lesson Id", l.title as "Lesson Title", u.first_name as "First Name", u.last_name as "Last Name", 
u.email as "Email Address", c.name as "Company Name", lt.created_at as "Date Requested",


IF(u.discr = "professionalUser", "YES", "NO") as "Is Professional",
IF(u.discr = "educatorUser", "YES", "NO") as "Is Educator",
IF(u.discr = "schoolAdministrator", "YES", "NO") as "Is School Administrator",

CASE
	WHEN u.discr = "professionalUser" THEN NULL
	WHEN u.discr = "educatorUser" THEN eu_school.name
	WHEN u.discr = "schoolAdministrator" THEN sa_school.name
END as "School",

CASE 
	WHEN u.discr = "professionalUser" THEN pu_region.id 
	WHEN u.discr = "educatorUser" THEN eu_region.id 
	WHEN u.discr = "schoolAdministrator" THEN sa_region.id
END as "Region"

FROM lesson l INNER JOIN lesson_teachable lt on l.id = lt.lesson_id
INNER JOIN user u on lt.user_id = u.id

/* Professional User Joins */
LEFT JOIN professional_user pu on u.id = pu.id
LEFT JOIN company c on pu.company_id = c.id
LEFT JOIN professional_user_region pur on pur.professional_user_id = pu.id
LEFT JOIN region pu_region on pu_region.id = pur.region_id

/* Educator Joins */
LEFT JOIN educator_user eu on u.id = eu.id
LEFT JOIN school eu_school on eu_school.id = eu.school_id
LEFT JOIN region eu_region on eu_school.region_id = eu_region.id

/* School Administrator Joins */
LEFT JOIN school_administrator sa on u.id = sa.id
LEFT JOIN school_school_administrator ssa on ssa.school_administrator_id = sa.id
LEFT JOIN school sa_school on sa_school.id = ssa.school_id
LEFT JOIN region sa_region on sa_school.region_id = sa_region.id

/* Regional Coordinator Joins */
LEFT JOIN regional_coordinator rc on u.id = rc.id
LEFT JOIN region rc_region on rc.region_id = rc_region.id

WHERE (u.discr = "educatorUser" OR u.discr = "schoolAdministrator") :regions',

                'filters' => [
                    ':regions' => function () use ($user) {

                        $regionIds = [];
                        if ($user->isRegionalCoordinator()) {
                            /** @var RegionalCoordinator $user */
                            $regionIds[] = $user->getRegion()->getId();
                        }

                        if (!empty($regionIds)) {
                            $regionIdString = implode("','", $regionIds);

                            return " AND ( eu_region.id IN('$regionIdString') OR sa_region.id IN('$regionIdString') )";
                        }

                        return '';
                    },
                ],

            ],
            'LESSONS_I_CAN_TEACH' => [
                'title' => 'Lessons Professionals Can Teach',
                'name' => 'LESSONS_I_CAN_TEACH',
                'roles' => [
                    'ROLE_ADMIN_USER',
                    'ROLE_SITE_ADMIN_USER',
                    'ROLE_REGIONAL_COORDINATOR_USER',
                ],
                'query' => 'select l.id as "Lesson Id", l.title as "Lesson Title", u.first_name as "First Name", u.last_name as "Last Name", 
u.email as "Email Address", c.name as "Company Name", lt.created_at as "Date Requested",


IF(u.discr = "professionalUser", "YES", "NO") as "Is Professional",
IF(u.discr = "educatorUser", "YES", "NO") as "Is Educator",
IF(u.discr = "schoolAdministrator", "YES", "NO") as "Is School Administrator",

CASE
	WHEN u.discr = "professionalUser" THEN NULL
	WHEN u.discr = "educatorUser" THEN eu_school.name
	WHEN u.discr = "schoolAdministrator" THEN sa_school.name
END as "School",

CASE 
	WHEN u.discr = "professionalUser" THEN pu_region.id 
	WHEN u.discr = "educatorUser" THEN eu_region.id 
	WHEN u.discr = "schoolAdministrator" THEN sa_region.id
END as "Region"

FROM lesson l INNER JOIN lesson_teachable lt on l.id = lt.lesson_id
INNER JOIN user u on lt.user_id = u.id

/* Professional User Joins */
LEFT JOIN professional_user pu on u.id = pu.id
LEFT JOIN company c on pu.company_id = c.id
LEFT JOIN professional_user_region pur on pur.professional_user_id = pu.id
LEFT JOIN region pu_region on pu_region.id = pur.region_id

/* Educator Joins */
LEFT JOIN educator_user eu on u.id = eu.id
LEFT JOIN school eu_school on eu_school.id = eu.school_id
LEFT JOIN region eu_region on eu_school.region_id = eu_region.id

/* School Administrator Joins */
LEFT JOIN school_administrator sa on u.id = sa.id
LEFT JOIN school_school_administrator ssa on ssa.school_administrator_id = sa.id
LEFT JOIN school sa_school on sa_school.id = ssa.school_id
LEFT JOIN region sa_region on sa_school.region_id = sa_region.id

/* Regional Coordinator Joins */
LEFT JOIN regional_coordinator rc on u.id = rc.id
LEFT JOIN region rc_region on rc.region_id = rc_region.id

WHERE u.discr = "professionalUser" :regions',

                'filters' => [
                    ':regions' => function () use ($user) {

                        $regionIds = [];
                        if ($user->isRegionalCoordinator()) {
                            /** @var RegionalCoordinator $user */
                            $regionIds[] = $user->getRegion()->getId();
                        }

                        if (!empty($regionIds)) {
                            $regionIdString = implode("','", $regionIds);

                            return " AND pu_region.id IN('$regionIdString')";
                        }

                        return '';
                    },
                ],
            ],

        ];

        if (!$reportName || !isset($reports[$reportName])) {

            $downloadableReports = [];
            foreach ($reports as $report) {

                if (is_callable($report['roles']) && !$report['roles']()) {
                    continue;
                }

                if (is_array($report['roles']) && empty(array_intersect($user->getRoles(), $report['roles']))) {
                    continue;
                }


                $downloadableReports[] = $report;
            }

            return $this->render(
                'report/index.html.twig', [
                    'user' => $user,
                    'reports' => $downloadableReports
                    //'form' => $form->createView(),
                ]
            );
        }

        $report = $reports[$reportName];

        if (is_callable($report['roles']) && !$report['roles']()) {
            $this->addFlash(
                'error', 'You do not have proper permissions to view this report. 
                Contact system administrator if you believe this is in error.'
            );

            return $this->redirectToRoute('report_index');
        }

        if (is_array($report['roles']) && empty(array_intersect($user->getRoles(), $report['roles']))) {
            $this->addFlash(
                'error', 'You do not have proper permissions to view this report. 
                Contact system administrator if you believe this is in error.'
            );

            return $this->redirectToRoute('report_index');
        }


        if (!empty($report['query']) && is_string($report['query'])) {

            $query   = $reports[$reportName]['query'];
            $filters = !empty($reports[$reportName]['filters']) ? $reports[$reportName]['filters'] : [];

            $query = preg_replace_callback(
                '/(\:[a-zA-Z\-\_]+)/', function ($matches) use ($filters) {
                $slug = $matches[0];

                if (empty($filters[$slug])) {
                    return '';
                }

                if (is_callable($filters[$slug])) {
                    return $filters[$slug]();
                }

                return '';
            }, $query
            );

            $results = $this->reportRepository->get($query);

            $response = new Response($this->serializer->encode($results, 'csv'));

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', "attachment; filename={$reportName}.csv");

            return $response;

        }

        if (!empty($report['query']) && is_callable($report['query'])) {

            $results = $report['query']();

            if (empty($results)) {
                $results[] = ['Report ' . $reportName => 'Zero Results'];
            }

            $response = new Response($this->serializer->encode($results, 'csv'));

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', "attachment; filename={$reportName}.csv");

            return $response;

        }

        /*      $role = new RolesWillingToFulfill();

              $form = $this->createForm(
                  RolesFormType::class, $role, [
                                          'method' => 'POST',
                                      ]
              );

              $form->handleRequest($request);

              if ($form->isSubmitted() && $form->isValid()) {
                  $role = $form->getData();
                  $role->setInRoleDropdown(true);
                  $role->setEventName($role->getName());

                  $this->entityManager->persist($role);
                  $this->entityManager->flush();
                  $this->addFlash('success', 'New role has been created.');

                  return $this->redirectToRoute('admin_role_new');
              }*/
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER"})
     * @Route("/email-log/download", name="admin_email_log_download")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function emailLogDownload(Request $request, SerializerInterface $serializer)
    {

        /** @var User $user */
        $user = $this->getUser();

        $emailLogs = $this->entityManager->getRepository(EmailLog::class)->findAll();

        $logs[] =
            [
                'from',
                'to',
                'subject',
                'status',
                'date',
            ];

        foreach ($emailLogs as $emailLog) {
            $logs[] = [
                $emailLog->getFromEmail(),
                $emailLog->getToEmail(),
                $emailLog->getSubject(),
                $emailLog->getStatus(),
                $emailLog->getCreatedAt()->format('m/d/Y h:i:s A'),
            ];
        }

        $response = new Response(
            $serializer->encode(
                $logs, 'csv', [
                    \Symfony\Component\Serializer\Encoder\CsvEncoder::NO_HEADERS_KEY => true,
                ]
            )
        );

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=email_log.csv");

        return $response;

    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER"})
     * @Route("/email-log/users/{id}/view", name="admin_email_log_view")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function viewEmails(Request $request, User $user)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $emailLogs = [];
        if ($user->getEmail()) {
            $emailLogs = $this->entityManager->getRepository(EmailLog::class)->findBy(
                [
                    'toEmail' => $user->getEmail(),
                ]
            );
        }

        return $this->render(
            'admin/email_logs.html.twig', [
                'user' => $user,
                'loggedInUser' => $loggedInUser,
                'emailLogs' => $emailLogs,
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER"})
     * @Route("/email-log/{id}/view", name="admin_email_log_view_singular")
     * @param Request  $request
     *
     * @param EmailLog $emailLog
     *
     * @return string
     */
    public function viewSingularEmail(Request $request, EmailLog $emailLog)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        return new Response($emailLog->getBody());
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/event-types/list", name="admin_event_types_list")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function listEventType(Request $request)
    {
        $user = $this->getUser();

        $roles = $this->rolesWillingToFulfillRepository->findBy(array (), array ('name' => 'ASC'));
        // $roles = $this->rolesWillingToFulfillRepository->findBy(
        //         ['userRole' => $v[0]],
        //         ['position' => 'ASC']
        //     );
        // }

        return $this->render(
            'admin/list_event_type.html.twig', [
                'user' => $user,
                'roles' => $roles,
            ]
        );
    }


    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/event-types/new", name="admin_event_types_new")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newEventType(Request $request)
    {

        $user = $this->getUser();
        $role = new RolesWillingToFulfill();

        $form = $this->createForm(
            EventTypeFormType::class, $role, [
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $role RolesWillingToFulfill */
            $role = $form->getData();
            $role->setEventName($role->getName());

            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $this->addFlash('success', 'New experience type has been created.');

            return $this->redirectToRoute('admin_event_types_list');
        }

        return $this->render(
            'admin/new_event_type.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/event-types/{id}/edit", name="admin_event_types_edit", options = { "expose" = true })
     * @param Request               $request
     * @param RolesWillingToFulfill $role
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function editEventType(Request $request, RolesWillingToFulfill $role)
    {

        $user = $this->getUser();


        $form = $this->createForm(
            EventTypeFormType::class, $role, [
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $role RolesWillingToFulfill */
            $role = $form->getData();
            $role->setEventName($role->getName());

            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $this->addFlash('success', 'Experience type has been edited.');

            return $this->redirectToRoute('admin_event_types_list');
        }

        return $this->render(
            'admin/edit_event_type.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER", "ROLE_PROFESSIONAL_USER"})
     * @Route("/experience-satisfaction-dashboard", name="report_experience_satisfaction_dashboard")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function experienceSatisfactionDashboard(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $dashboardOrder = $request->request->get('sortableData', null);

        if ($dashboardOrder) {
            $originalDashboardOrder = $user->getDashboardOrder() ?? [];

            if ($request->query->has('top')) {
                $originalDashboardOrder[AbstractDashboard::PAGE_FEEDBACK_POSITION_1] = $dashboardOrder;
            } else {
                if ($request->query->has('bottom')) {
                    $originalDashboardOrder[AbstractDashboard::PAGE_FEEDBACK_POSITION_2] = $dashboardOrder;
                }
            }

            $user->setDashboardOrder($originalDashboardOrder);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                ],
                Response::HTTP_OK
            );
        }

        // Let's setup some default date filters if none are passed up
        $data           = null;
        $filters        = $request->query->get('item_filter', []);
        $eventStartDate = $filters['eventStartDate'] ?? [];
        if (empty($eventStartDate['left_date']) || empty($eventStartDate['right_date'])) {
            $leftDate  = new \DateTime('-1 month');
            $rightDate = new \DateTime('now');

            $data = [
                'eventStartDate' => [
                    'left_date' => $leftDate,
                    'right_date' => $rightDate,
                ],
            ];
        } else {
            $leftDate  = $eventStartDate['left_date'];
            $rightDate = $eventStartDate['right_date'];
        }

        // depending on the user role type that will determine which filters we show.
        $form = $this->createForm(
            FeedbackFilterType::class, $data, [
                'method' => 'GET',
            ]
        );

        $form->handleRequest($request);

        // make sure you don't pull feedback that has been marked as deleted
        $filterBuilder = $this->feedbackRepository->createQueryBuilder('f');

        if ($user->isProfessional()) {
            /** @var ProfessionalUser $user */
            $company   = $user->getCompany();
            $companyId = $company->getId();


            $filterBuilder->where('f.companies LIKE :companyId')
                          ->andWhere('u.')
                          ->setParameter('roles', '%"' . User::ROLE_EDUCATOR_USER . '"%');


        }


        $this->filterBuilder->addFilterConditions($form, $filterBuilder);

        $filterQuery = $filterBuilder->getQuery();

        $feedbackCollection = new FeedbackCollection($filterQuery->getResult());

        $defaultDashboards = [
            AbstractDashboard::DASHBOARD_SUMMARY,
            AbstractDashboard::DASHBOARD_STUDENT_INTEREST_IN_WORKING_FOR_COMPANY,
            AbstractDashboard::DASHBOARD_EXPERIENCE_RATING,
            AbstractDashboard::DASHBOARD_NPS_SCORE,
            AbstractDashboard::DASHBOARD_EXPERIENCE_ENJOYABLE_AND_ENGAGING,
            AbstractDashboard::DASHBOARD_LEARNED_SOMETHING_NEW,
            AbstractDashboard::DASHBOARD_PROVIDED_CAREER_INSIGHT,
            AbstractDashboard::DASHBOARD_LIKELIHOOD_TO_RECOMMEND_A_FRIEND,
            AbstractDashboard::DASHBOARD_PROMOTER_NEEUTRAL_DETRACTOR,
        ];

        $dashboardOrder          = $user->getDashboardOrder() ?? [];
        $userSavedPos1Dashboards = $dashboardOrder[AbstractDashboard::PAGE_FEEDBACK_POSITION_1] ?? [];
        $userSavedPos2Dashboards = $dashboardOrder[AbstractDashboard::PAGE_FEEDBACK_POSITION_2] ?? [];

        $charts = [];
        foreach ($defaultDashboards as $defaultDashboard) {

            if (!class_exists($defaultDashboard)) {
                continue;
            }

            // todo let's cache the computation here for the given feedback collection ids right or for the request url or filters or something?
            // todo create an md5 hash right? Double check this to make sure this will work. md5 === md5, etc

            /** @var AbstractDashboard $dashboardInstance */
            $dashboardInstance = new $defaultDashboard($feedbackCollection);

            if (($position = array_search($defaultDashboard, $userSavedPos1Dashboards)) !== false) {
                $dashboardInstance->setPosition($position);
            }

            if (($position = array_search($defaultDashboard, $userSavedPos2Dashboards)) !== false) {
                $dashboardInstance->setPosition($position);
            }

            $charts[] = $dashboardInstance;
        }

        $showFilters = $request->query->has('item_filter');

        return $this->render(
            'report/dashboard/experience_satisfaction.html.twig', [
                'user' => $user,
                'charts' => $charts,
                'leftDate' => $leftDate,
                'rightDate' => $rightDate,
                'showFilters' => $showFilters,
                'form' => $form->createView(),
                'clearFormUrl' => $this->generateUrl('report_experience_satisfaction_dashboard'),
                'request' => $request
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER", "ROLE_PROFESSIONAL_USER"})
     * @Route("/dashboard-filter", name="report_dashboard_filter")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function filter(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = null;

        $form = $this->createForm(
            FeedbackFilterType::class, $data, [
                'method' => 'GET',
            ]
        );

        $form->handleRequest($request);

        //$type = 'school';

/*        switch($type) {
            case 'region':
                $form = $this->createForm(
                    RegionFilterType::class, $data, [
                        'method' => 'GET',
                    ]
                );
                $template = 'report/dashboard/filter/region.html.twig';
                break;
            case 'school':
                $form = $this->createForm(
                    SchoolFilterType::class, $data, [
                        'method' => 'GET',
                    ]
                );
                $template = 'report/dashboard/filter/school.html.twig';
                break;
        }*/

        $template = 'report/dashboard/filter/school.html.twig';

        $form->handleRequest($request);

        return $this->render(
            $template, [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );

    }
}