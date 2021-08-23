<?php

namespace App\Controller;

use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Feedback;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\UserMeta;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Request as RequestEntity;

/**
 * Class DashboardController
 *
 * @package App\Controller
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="dashboard", methods={"GET"})
     * @param Request          $request
     * @param SessionInterface $session
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function indexAction(Request $request, SessionInterface $session)
    {

        /** @var User $user */
        $user = $loggedInUser = $this->getUser();

        $dashboards = [];

        if ($user->isAdmin()) {

            $dashboards = [
                'sites' => $this->siteRepository->findAll(),
            ];

        } elseif ($user->isRegionalCoordinator()) {
            /** @var RegionalCoordinator $user */
            $numberOfStudentsInRegion                               = count($this->studentUserRepository->getStudentsForRegion($user->getRegion()));
            $numberOfEducatorsInRegion                              = count($this->educatorUserRepository->getEducatorsForRegion($user->getRegion()));
            $numberOfSchoolAdminsInRegion                           = count($this->schoolAdministratorRepository->getSchoolAdminsForRegion($user->getRegion()));
            $schoolEventsByRegionGroupedBySchool                    = $this->schoolExperienceRepository->getNumberOfEventsGroupedBySchoolForRegion($user->getRegion());
            $companyEventsGroupedByPrimaryIndustry                  = $this->companyExperienceRepository->getNumberOfEventsGroupedByPrimaryIndustry();
            $numberOfRegistrationsGroupedByPrimaryIndustryForRegion = $this->companyExperienceRepository->getNumberOfRegistrationsGroupedByPrimaryIndustryForRegion($user->getRegion());

            $dashboards = [
                'numberOfStudentsInRegion' => $numberOfStudentsInRegion,
                'numberOfEducatorsInRegion' => $numberOfEducatorsInRegion,
                'numberOfSchoolAdminsInRegion' => $numberOfSchoolAdminsInRegion,
                'schoolEventsByRegionGroupedBySchool' => $schoolEventsByRegionGroupedBySchool,
                'companyEventsGroupedByPrimaryIndustry' => $companyEventsGroupedByPrimaryIndustry,
                'numberOfRegistrationsGroupedByPrimaryIndustryForRegion' => $numberOfRegistrationsGroupedByPrimaryIndustryForRegion,
            ];
        } elseif ($user->isSchoolAdministrator()) {
            /** @var SchoolAdministrator $user */
            $numberOfStudentsInSchoolNetwork                             = 0;
            $numberOfEducatorsInSchoolNetwork                            = 0;
            $dashboards['registrationsGroupedByPrimaryIndustryInSchool'] = [];
            foreach ($user->getSchools() as $school) {
                $numberOfStudentsInSchoolNetwork  += count($this->studentUserRepository->findBy(['school' => $school]));
                $numberOfEducatorsInSchoolNetwork += count($this->educatorUserRepository->findBy(['school' => $school]));

                $numberOfRegistrationsGroupedByPrimaryIndustryForSchool = $this->companyExperienceRepository->getNumberOfRegistrationsGroupedByPrimaryIndustryForSchool($school);

                // Get experiences for each school
                $schoolEvents   = $this->experienceRepository->getEventsBySchool($school);
                $schoolFeedback = [];

                foreach ($schoolEvents as $event) {

                    $allFeedback                            = $this->feedbackRepository->findByEvent($event);
                    $schoolFeedback[$event['id']]['events'] = $allFeedback;
                }

                $dashboards['registrationsGroupedByPrimaryIndustryInSchool'][$school->getId()] = [
                    'schoolName' => $school->getName(),
                    'school_id' => $school->getId(),
                    'registrationsGroupedByPrimaryIndustryInSchool' => $numberOfRegistrationsGroupedByPrimaryIndustryForSchool,
                    'schoolEvents' => $schoolEvents,
                    'schoolFeedback' => $schoolFeedback,
                ];


            }

            $companyEventsGroupedByPrimaryIndustry = $this->companyExperienceRepository->getNumberOfEventsGroupedByPrimaryIndustry();

            $dashboards = [
                'numberOfStudentsInSchoolNetwork' => $numberOfStudentsInSchoolNetwork,
                'numberOfEducatorsInSchoolNetwork' => $numberOfEducatorsInSchoolNetwork,
                'companyEventsGroupedByPrimaryIndustry' => $companyEventsGroupedByPrimaryIndustry,
                'events' => $dashboards['registrationsGroupedByPrimaryIndustryInSchool'],
            ];

        } elseif ($user->isStudent() || $user->isEducator()) {
            /** @var StudentUser|EducatorUser $user */
            $lessonFavorites                    = $this->lessonFavoriteRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
            $companyFavorites                   = $this->companyFavoriteRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
            $upcomingEventsRegisteredForByUser  = $this->experienceRepository->getUpcomingEventsRegisteredForByUser($user);
            $completedEventsRegisteredForByUser = $this->experienceRepository->getCompletedEventsRegisteredForByUser($user);
            $primaryIndustries                  = $this->industryRepository->findAll();

            $guestLectures = $this->teachLessonExperienceRepository->findBy([
                'school' => $user->getSchool(),
            ]);

            $dashboards = [
                'companyFavorites' => $companyFavorites,
                'lessonFavorites' => $lessonFavorites,
                'upcomingEventsRegisteredForByUser' => $upcomingEventsRegisteredForByUser,
                'completedEventsRegisteredForByUser' => $completedEventsRegisteredForByUser,
                'guestLectures' => $guestLectures,
                'eventsWithFeedback' => [],
                'eventsMissingFeedback' => [],
                'eventsWithFeedbackFromOthers' => [],
                'primaryIndustries' => $primaryIndustries,
            ];

            // let's see which events have feedback from the user and which don't
            foreach ($completedEventsRegisteredForByUser as $event) {


                // if($user->isStudent() && !$event instanceof StudentToMeetProfessionalExperience) {
                //     continue;
                // }
                // Commented out ER - 9/2/20

                $allFeedback = $this->feedbackRepository->findBy([
                    'experience' => $event,
                    'deleted' => false,
                ]);

                /** @var Feedback $feedback */
                foreach ($allFeedback as $feedback) {
                    if ($feedback->getUser()->getId() !== $user->getId()) {
                        $dashboards['eventsWithFeedbackFromOthers'][] = $event;
                        break;
                    }
                }

                $feedback = $this->feedbackRepository->findOneBy([
                    'user' => $user,
                    'experience' => $event,
                ]);

                if (!$feedback) {
                    $dashboards['eventsMissingFeedback'][] = [
                        'event' => $event,
                        'feedback' => $feedback,
                    ];
                } else {
                    if ($feedback->getDeleted() === false) {
                        $dashboards['eventsWithFeedback'][] = [
                            'event' => $event,
                            'feedback' => $feedback,
                        ];
                    }
                }
            }

        } elseif ($user->isProfessional()) {
            $completedEventsRegisteredForByUser = $this->experienceRepository->getCompletedEventsRegisteredForByUser($user);

            /** @var ProfessionalUser $user */
            $upcomingEventsRegisteredForByUser  = $this->experienceRepository->getUpcomingEventsRegisteredForByUser($user);
            $completedEventsRegisteredForByUser = $this->experienceRepository->getCompletedEventsRegisteredForByUser($user);
            $dashboards                         = [
                'myCompany' => $user->getCompany(),
                'eventsMissingFeedback' => [],
                'upcomingEventsRegisteredForByUser' => $upcomingEventsRegisteredForByUser,
                'completedEventsRegisteredForByUser' => $completedEventsRegisteredForByUser,
                'eventsWithFeedback' => [],
                'eventsWithFeedbackFromOthers' => [],
            ];

            foreach ($completedEventsRegisteredForByUser as $event) {

                $allFeedback = $this->feedbackRepository->findBy([
                    'experience' => $event,
                    'deleted' => false,
                ]);

                /** @var Feedback $feedback */
                foreach ($allFeedback as $feedback) {
                    if ($feedback->getUser()->getId() !== $user->getId()) {
                        $dashboards['eventsWithFeedbackFromOthers'][] = $event;
                        break;
                    }
                }

                $feedback = $this->feedbackRepository->findOneBy([
                    'user' => $user,
                    'experience' => $event,
                    'deleted' => false,
                ]);

                if (!$feedback) {
                    $dashboards['eventsMissingFeedback'][] = [
                        'event' => $event,
                        'feedback' => $feedback,
                    ];
                } else {
                    if ($feedback->getDeleted() === false) {
                        $dashboards['eventsWithFeedback'][] = [
                            'event' => $event,
                            'feedback' => $feedback,
                        ];
                    }
                }
            }

            $teachableLessonIds = [];
            foreach ($user->getLessonTeachables() as $lessonTeachable) {
                $teachableLessonIds[] = $lessonTeachable->getLesson()->getId();
            }
            $educatorsWhoFavoritedMyLessons               = $this->educatorUserRepository->findByFavoriteLessonIds($teachableLessonIds);
            $dashboards['educatorsWhoFavoritedMyLessons'] = $educatorsWhoFavoritedMyLessons;

            $userSecondaryIndustries = $user->getSecondaryIndustries();
            // Get relevant lessons for the user's secondary industry preferences
            $companiesWithOverlappingSecondaryIndustries               = $this->companyRepository->findBySecondaryIndustries($userSecondaryIndustries);
            $dashboards['companiesWithOverlappingSecondaryIndustries'] = $companiesWithOverlappingSecondaryIndustries;

            $dashboards['completedTeachLessonExperiences'] = $this->teachLessonExperienceRepository->getCompletedByUser($user);


            // let's see which events have feedback from the user and which don't
            foreach ($completedEventsRegisteredForByUser as $event) {
                $feedback = $this->feedbackRepository->findOneBy([
                    'user' => $user,
                    'experience' => $event,
                ]);

                // For now, just show Student to meet with professional events
                if ($event->getClassName() == 'StudentToMeetProfessionalExperience') {
                    if (!$feedback) {
                        $dashboards['eventsMissingFeedback'][] = [
                            'event' => $event,
                            'feedback' => $feedback,
                        ];
                    } else {
                        if ($feedback->getDeleted() === false) {
                            $dashboards['eventsWithFeedback'][] = [
                                'event' => $event,
                                'feedback' => $feedback,
                            ];
                        }
                    }
                }
            }
        }

        $lessons = $this->lessonRepository->findAllLessonsFromPastDays(7);

        // Get relevant events for the user's secondary industry preferences
        // TODO Possibly call findBySecondaryIndustries($secondaryIndustries, $limit = 6)  in
        //  the future to only pull event results if they express interest in that industry
        $schoolExperiences   = $this->schoolExperienceRepository->findAllFromPastDays(7);
        $schoolExperienceIds = [];
        foreach ($schoolExperiences as $schoolExperience) {
            $schoolExperienceIds[] = $schoolExperience['id'];
        }

        if ($loggedInUser->isSchoolAdministrator()) {
            $u_school_ids = [];
            foreach ($user->getSchools() as $school) {
                $u_school_ids[] = $school->getId();
            }
            $schoolExperiences = $this->schoolExperienceRepository->findBy(['id' => $schoolExperienceIds,
                                                                            'school' => $u_school_ids,
                                                                            'cancelled' => false,
            ]);
        } else {
            $schoolExperiences = $this->schoolExperienceRepository->findBy(['id' => $schoolExperienceIds,
                                                                            'cancelled' => false,
            ]);
        }

        $companyExperiences   = $this->companyExperienceRepository->findAllFromPastDays(7);
        $companyExperienceIds = [];
        foreach ($companyExperiences as $companyExperience) {
            $companyExperienceIds[] = $companyExperience['id'];
        }
        $companyExperiences = $this->companyExperienceRepository->findBy(['id' => $companyExperienceIds,
                                                                          'cancelled' => false,
        ]);


        $useRegionFiltering = false;
        $regions            = [];
        if ($loggedInUser->isSchoolAdministrator()) {

            $useRegionFiltering = true;

            /** @var SchoolAdministrator $user */
            foreach ($loggedInUser->getSchools() as $school) {

                if (!$school->getRegion()) {
                    continue;
                }

                $regions[] = $school->getRegion()->getId();
            }
        }

        if ($loggedInUser->isProfessional()) {

            $useRegionFiltering = true;

            /** @var ProfessionalUser $loggedInUser */

            foreach ($loggedInUser->getRegions() as $region) {

                $regions[] = $region->getId();
            }
        }

        if ($loggedInUser->isStudent() || $loggedInUser->isEducator()) {

            $useRegionFiltering = true;

            /** @var StudentUser|EducatorUser $user */

            if ($loggedInUser->getSchool() && $loggedInUser->getSchool()->getRegion()) {
                $regions[] = $loggedInUser->getSchool()->getRegion()->getId();
            }
        }

        $regions = array_unique($regions);

        if ($useRegionFiltering) {
            $companyExperiences = array_filter($companyExperiences, function (CompanyExperience $companyExperience) use
            (
                $regions
            ) {

                if ($companyExperience->isVirtual()) {
                    return true;
                }

                if (!$companyExperience->getCompany()) {
                    return false;
                }

                $hasMatch = false;
                foreach ($companyExperience->getCompany()->getRegions() as $region) {
                    if (in_array($region->getId(), $regions)) {
                        $hasMatch = true;
                    }
                }

                if (!$hasMatch) {
                    return false;
                }

                return true;
            });
        }

        $companyExperiences = array_values($companyExperiences);

        /*$callToActions = $this->renderView('calltoaction/index.json.twig', [
            'user' => $user
        ]);

        $callToActions = json_decode($callToActions, true);*/

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'dashboards' => $dashboards,
            'lessons' => $lessons,
            'schoolExperiences' => $schoolExperiences,
            'companyExperiences' => $companyExperiences,
        ]);
    }


    public function myRequests(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $requestsThatNeedMyApproval = $this->requestRepository->getRequestsThatNeedMyApproval($user, true);

        return $this->render('dashboard/my_requests.html.twig', [
            'user' => $user,
            'requestsThatNeedMyApproval' => $requestsThatNeedMyApproval,
        ]);

    }

    public function experiences(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $experienceIds     = [];
        $schoolExperiences = $this->schoolExperienceRepository->findBy(['cancelled' => false], [
            'createdAt' => 'DESC',
        ], 50);

        $companyExperiences = $this->companyExperienceRepository->findBy(['cancelled' => false,], [
            'createdAt' => 'DESC',
        ], 50);

        foreach ($schoolExperiences as $schoolExperience) {
            $experienceIds[] = $schoolExperience->getId();
        }

        foreach ($companyExperiences as $companyExperience) {
            $experienceIds[] = $companyExperience->getId();
        }

        $experiences = $this->experienceRepository->findBy([
            'id' => $experienceIds,
            'cancelled' => false,
        ], ['createdAt' => 'DESC', 'title' => 'ASC']);

        return $this->render('dashboard/experiences.html.twig', [
            'user' => $user,
            'experiences' => $experiences,
        ]);

    }

    public function topics(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $lessons = $this->lessonRepository->findBy([
            'deleted' => false,
        ], [
            'createdAt' => 'DESC',
            'title' => 'ASC',
        ], 50);

        return $this->render('dashboard/topics.html.twig', [
            'user' => $user,
            'lessons' => $lessons,
        ]);

    }

    public function volunteerProfessionals(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isEducator() && $school = $user->getSchool()) {
            $volunteerProfessionals = $this->professionalUserRepository->getBySchool($school);
        } else {
            $volunteerProfessionals = $this->professionalUserRepository->findBy([], [
                'createdAt' => 'DESC',
                'lastName' => 'ASC',
            ], 50);
        }

        return $this->render('dashboard/volunteer_professionals.html.twig', [
            'user' => $user,
            'volunteerProfessionals' => $volunteerProfessionals,
        ]);

    }

    public function guidesAndBestPractices(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('dashboard/guides_and_best_practices.html.twig', [
            'user' => $user,
        ]);
    }

    public function jobBoard(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $requestEntities = $this->requestRepository->findBy([
            'requestType' => RequestEntity::REQUEST_TYPE_JOB_BOARD,
            'published' => true,
        ], ['createdAt' => 'DESC']);

        $hiddenRequests = $this->userMetaRepository->findBy([
            'user' => $user,
            'name' => UserMeta::HIDE_REQUEST,
        ]);

        $hiddenRequestIds = array_map(function (UserMeta $hiddenRequest) {
            return (int) $hiddenRequest->getValue();
        }, $hiddenRequests);

        $requestEntities = array_filter($requestEntities, function (RequestEntity $requestEntity) use ($hiddenRequestIds) {
            return !in_array($requestEntity->getId(), $hiddenRequestIds, true);
        });

        if (!count($requestEntities)) {
            return new Response("");
        }

        return $this->render('dashboard/job_board.html.twig', [
            'user' => $user,
            'requestEntities' => $requestEntities,
        ]);
    }
}
