<?php

namespace App\Controller;

use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Form\AdminProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\RegionalCoordinatorRepository;
use App\Repository\UserRepository;
use App\Repository\IndustryRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class DashboardController
 * @package App\Controller
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="dashboard", methods={"GET"})
     * @param Request $request
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function indexAction(Request $request, SessionInterface $session) {

        /** @var User $user */
        $user = $this->getUser();

        $dashboards = [];

        if($user->isRegionalCoordinator()) {
            /** @var RegionalCoordinator $user */
            $numberOfStudentsInRegion = count($this->studentUserRepository->getStudentsForRegion($user->getRegion()));
            $numberOfEducatorsInRegion = count($this->educatorUserRepository->getEducatorsForRegion($user->getRegion()));
            $numberOfSchoolAdminsInRegion = count($this->schoolAdministratorRepository->getSchoolAdminsForRegion($user->getRegion()));
            $schoolEventsByRegionGroupedBySchool = $this->schoolExperienceRepository->getEventsByRegionGroupedBySchool($user->getRegion());
            $companyEventsGroupedByPrimaryIndustry = $this->companyExperienceRepository->getEventsGroupedByPrimaryIndustry();
            $numberOfRegistrationsGroupedByPrimaryIndustryForRegion = $this->companyExperienceRepository->getNumberOfRegistrationsGroupedByPrimaryIndustryForRegion($user->getRegion());
            $schoolEventsByRegionGroupedBySchoolIncludingZero = [];
            $companyEventsGroupedByPrimaryIndustryIncludingZero = [];
            $numberOfRegistrationsGroupedByPrimaryIndustryForRegionIncludingZero = [];

            // @TODO: Josh Clean Up
            foreach ( $user->getRegion()->getSchools() as $school ) {
                $school_name = $school->getName();
                $default = [
                    'num_of_school_events' => 0,
                    'school_name' => $school_name
                ];

                $school_found = array_values(array_filter( $schoolEventsByRegionGroupedBySchool, function($result) use ( $school_name ) {
                    return $result['school_name'] === $school_name;
                }));
                array_push( $schoolEventsByRegionGroupedBySchoolIncludingZero, array_merge( $default, !empty( $school_found ) ? $school_found[0] : [] ) );
            }

            $primaryIndustries = $this->industryRepository->findAll();
            foreach ( $primaryIndustries as $industry ) {
                $industry_name = $industry->getName();
                $default = [
                    'num_of_company_events' => 0,
                    'primary_industry_name' => $industry_name
                ];

                $event_found = array_values( array_filter( $companyEventsGroupedByPrimaryIndustry, function($result) use ( $industry_name ) {
                    return $result['primary_industry_name'] === $industry_name;
                }));
                array_push( $companyEventsGroupedByPrimaryIndustryIncludingZero, array_merge( $default, !empty( $event_found ) ? $event_found[0] : [] ) );
            }
            foreach ( $primaryIndustries as $industry ) {
                $industry_name = $industry->getName();
                $default = [
                    'number_of_registrations' => 0,
                    'primary_industry_name' => $industry_name
                ];

                $registration_found = array_values(array_filter( $numberOfRegistrationsGroupedByPrimaryIndustryForRegion, function($result) use ( $industry_name ) {
                    return $result['primary_industry_name'] === $industry_name;
                }));
                array_push( $numberOfRegistrationsGroupedByPrimaryIndustryForRegionIncludingZero, array_merge( $default, !empty( $registration_found ) ? $registration_found[0] : [] ) );
            }
            // END Josh Clean Up

            $dashboards = [
                'numberOfStudentsInRegion' => $numberOfStudentsInRegion,
                'numberOfEducatorsInRegion' => $numberOfEducatorsInRegion,
                'numberOfSchoolAdminsInRegion' => $numberOfSchoolAdminsInRegion,
                'schoolEventsByRegionGroupedBySchool' => $schoolEventsByRegionGroupedBySchool,
                'schoolEventsByRegionGroupedBySchoolIncludingZero' => $schoolEventsByRegionGroupedBySchoolIncludingZero,
                'companyEventsGroupedByPrimaryIndustry' => $companyEventsGroupedByPrimaryIndustry,
                'companyEventsGroupedByPrimaryIndustryIncludingZero' => $companyEventsGroupedByPrimaryIndustryIncludingZero,
                'numberOfRegistrationsGroupedByPrimaryIndustryForRegion' => $numberOfRegistrationsGroupedByPrimaryIndustryForRegion,
                'numberOfRegistrationsGroupedByPrimaryIndustryForRegionIncludingZero' => $numberOfRegistrationsGroupedByPrimaryIndustryForRegionIncludingZero
            ];
        } elseif ($user->isSchoolAdministrator()) {
            /** @var SchoolAdministrator $user */
            $numberOfStudentsInSchoolNetwork = 0;
            $numberOfEducatorsInSchoolNetwork = 0;
            $dashboards['registrationsGroupedByPrimaryIndustryInSchool'] = [];
            foreach($user->getSchools() as $school) {
                $numberOfStudentsInSchoolNetwork += count($this->studentUserRepository->findBy(['school' => $school]));
                $numberOfEducatorsInSchoolNetwork+= count($this->educatorUserRepository->findBy(['school' => $school]));

                $registrationsGroupedByPrimaryIndustryInSchool = $this->companyExperienceRepository->getNumberOfRegistrationsGroupedByPrimaryIndustryInSchool($school);

                $dashboards['registrationsGroupedByPrimaryIndustryInSchool'][$school->getId()] = [
                    'schoolName' => $school->getName(),
                    'school_id' => $school->getId(),
                    'registrationsGroupedByPrimaryIndustryInSchool' => $registrationsGroupedByPrimaryIndustryInSchool
                ];
            }

            $companyEventsGroupedByPrimaryIndustry = $this->companyExperienceRepository->getEventsGroupedByPrimaryIndustry();

            $dashboards = [
                'numberOfStudentsInSchoolNetwork' => $numberOfStudentsInSchoolNetwork,
                'numberOfEducatorsInSchoolNetwork' => $numberOfEducatorsInSchoolNetwork,
                'companyEventsGroupedByPrimaryIndustry' => $companyEventsGroupedByPrimaryIndustry
            ];

        } elseif ($user->isStudent() || $user->isEducator()) {
            /** @var StudentUser|EducatorUser $user */
            $lessonFavorites = $this->lessonFavoriteRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
            $companyFavorites = $this->companyFavoriteRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
            $upcomingEventsRegisteredForByUser = $this->experienceRepository->getUpcomingEventsRegisteredForByUser($user);
            $completedEventsRegisteredForByUser = $this->experienceRepository->getCompletedEventsRegisteredForByUser($user);
            $primaryIndustries = $this->industryRepository->findAll();

            $guestLectures = $this->teachLessonExperienceRepository->findBy([
                'school' => $user->getSchool()
            ]);

            $dashboards = [
                'companyFavorites' => $companyFavorites,
                'lessonFavorites' => $lessonFavorites,
                'upcomingEventsRegisteredForByUser' => $upcomingEventsRegisteredForByUser,
                'completedEventsRegisteredForByUser' => $completedEventsRegisteredForByUser,
                'guestLectures' => $guestLectures,
                'eventsWithFeedback' => [],
                'eventsMissingFeedback' => [],
                'primaryIndustries' => $primaryIndustries
            ];

            // let's see which events have feedback from the user and which don't
            foreach($completedEventsRegisteredForByUser as $event) {
                if($event instanceof CompanyExperience) {
                    if($user->isStudent()) {
                        $feedback = $this->studentReviewCompanyExperienceFeedbackRepository->findOneBy([
                            'student' => $user,
                            'companyExperience' => $event
                        ]);

                        if(!$feedback) {
                            $dashboards['eventsMissingFeedback'][] = $event;
                        } else {
                            $dashboards['eventsWithFeedback'][] = $event;
                        }

                    } elseif ($user->isEducator()) {
                        $feedback = $this->educatorReviewCompanyExperienceFeedbackRepository->findOneBy([
                            'educator' => $user,
                            'companyExperience' => $event
                        ]);

                        if(!$feedback) {
                            $dashboards['eventsMissingFeedback'][] = $event;
                        } else {
                            $dashboards['eventsWithFeedback'][] = $event;
                        }
                    }
                } elseif ($event instanceof TeachLessonExperience) {
                    if($user->isStudent()) {
                        $feedback = $this->studentReviewTeachLessonExperienceFeedbackRepository->findOneBy([
                            'student' => $user,
                            'teachLessonExperience' => $event
                        ]);

                        if(!$feedback) {
                            $dashboards['eventsMissingFeedback'][] = $event;
                        } else {
                            $dashboards['eventsWithFeedback'][] = $event;
                        }

                    } elseif ($user->isEducator()) {
                        $feedback = $this->educatorReviewTeachLessonExperienceFeedbackRepository->findOneBy([
                            'educator' => $user,
                            'teachLessonExperience' => $event
                        ]);

                        if(!$feedback) {
                            $dashboards['eventsMissingFeedback'][] = $event;
                        } else {
                            $dashboards['eventsWithFeedback'][] = $event;
                        }
                    }
                }
            }
        } elseif ($user->isProfessional()) {
            /** @var ProfessionalUser $user */
            $dashboards = [
                'myCompany' => $user->getCompany(),
            ];

            $teachableLessonIds = [];
            foreach($user->getLessonTeachables() as $lessonTeachable) {
                $teachableLessonIds[] = $lessonTeachable->getLesson()->getId();
            }
            $educatorsWhoFavoritedMyLessons = $this->educatorUserRepository->findByFavoriteLessonIds($teachableLessonIds);
            $dashboards['educatorsWhoFavoritedMyLessons'] = $educatorsWhoFavoritedMyLessons;

            $userSecondaryIndustries = $user->getSecondaryIndustries();
            // Get relevant lessons for the user's secondary industry preferences
            $companiesWithOverlappingSecondaryIndustries = $this->companyRepository->findBySecondaryIndustries($userSecondaryIndustries);
            $dashboards['companiesWithOverlappingSecondaryIndustries'] = $companiesWithOverlappingSecondaryIndustries;
        }

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'dashboards' => $dashboards
        ]);
    }
}
