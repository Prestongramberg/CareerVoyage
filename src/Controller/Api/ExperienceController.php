<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\StudentUser;
use App\Entity\SystemUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Service\FilterGenerator;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ExperienceController
 *
 * @package App\Controller
 * @Route("/api")
 */
class ExperienceController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/experiences", name="get_experiences", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getExperiences(Request $request)
    {
        $loggedInUser       = $this->getUser();
        $companyExperiences = [];
        $schoolExperiences  = [];
        $userExperiences    = [];
        $userId             = $request->query->get('userId', null);
        $schoolId           = $request->query->get('schoolId', null);
        /** @var User $user */
        if ($schoolId && $school = $this->schoolRepository->find($schoolId)) {
            $schoolExperiences = $this->schoolExperienceRepository->findBy(
                [
                    'school' => $school,
                ]
            );
            // $companyExperiences = $this->companyExperienceRepository->getForSchool($school);
        } else {
            if ($userId) {
                /** @var User $user */
                $user            = $userId ? $this->userRepository->find($userId) : $this->getUser();
                $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUser($user);
                if ($user && $user->isStudent() && $user->getSchool()) {
                    // get any school experiences that are part of your school
                    $schoolExperiences = $this->schoolExperienceRepository->findBy(
                        [
                            'school' => $user->getSchool(),
                        ]
                    );
                }
            } else {
                // Everyone sees all company events
                $companyExperiences = $this->companyExperienceRepository->findAll();

                if ($loggedInUser->isSchoolAdministrator()) {
                    /** @var SchoolAdministrator $loggedInUser * */
                    // School Administrator will see all school events that they manage
                    foreach ($loggedInUser->getSchools() as $school) {
                        $experiences       = $this->schoolExperienceRepository->findBy(
                            [
                                'school' => $school,
                            ]
                        );
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                } else {
                    if ($loggedInUser->isEducator() || $loggedInUser->isStudent()) {
                        // Educator & students will see their school events
                        /** @var StudentUser|EducatorUser $loggedInUser * */
                        $school            = $loggedInUser->getSchool();
                        $schoolExperiences = $this->schoolExperienceRepository->findBy(
                            [
                                'school' => $school,
                            ]
                        );
                    } else {
                        if ($loggedInUser->isProfessional()) {
                            // Professional will see all school events that they VOLUNTEER AT
                            /** @var ProfessionalUser $loggedInUser * */
                            foreach ($loggedInUser->getSchools() as $school) {
                                $experiences       = $this->schoolExperienceRepository->findBy(
                                    [
                                        'school' => $school,
                                    ]
                                );
                                $schoolExperiences = array_merge($schoolExperiences, $experiences);
                            }
                        }
                    }
                }
            }
        }

        $experiences = array_merge($schoolExperiences, $companyExperiences, $userExperiences);

        $json    = $this->serializer->serialize(
            $experiences, 'json', [
                            'groups' => [
                                'EXPERIENCE_DATA',
                                'ALL_USER_DATA',
                            ],
                        ]
        );
        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data'    => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Example Request: http://pintex.test/api/experiences-by-radius?zipcode=54017
     *
     * @Route("/experiences-by-radius", name="get_experiences_by_radius", methods={"GET"}, options = { "expose" = true })
     * @param Request         $request
     *
     * @param FilterGenerator $filterGenerator
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getExperiencesByRadius(Request $request, FilterGenerator $filterGenerator)
    {
        $loggedInUser       = $this->getUser();
        $companyExperiences = [];
        $schoolExperiences  = [];
        $userExperiences    = [];
        $userId             = $request->query->get('userId', null);
        $schoolId           = $request->query->get('schoolId', null);
        $zipcode            = $request->query->get('zipcode', null);
        $radius             = $request->query->get('radius', 70);
        $startDate          = $request->query->get('start', null);
        $endDate            = $request->query->get('end', null);
        $searchQuery        = $request->query->get('searchQuery', '');
        $eventType          = $request->query->get('eventType', null);
        $industry           = $request->query->get('industry', null);
        $secondaryIndustry  = $request->query->get('secondaryIndustry', null);
        $lng                = null;
        $lat                = null;
        $latN               = null;
        $latS               = null;
        $lonE               = null;
        $lonW               = null;

        // todo query filters and cache them right here...
        $filters = $filterGenerator->generate(
            [
                FilterGenerator::INDUSTRY_FILTER,
                FilterGenerator::SECONDARY_INDUSTRY_FILTER,
                FilterGenerator::EVENT_TYPE_FILTER,
            ]
        );

        $regions            = [];
        if ($loggedInUser->isSchoolAdministrator()) {

            /** @var SchoolAdministrator $user */
            foreach ($loggedInUser->getSchools() as $school) {

                if (!$school->getRegion()) {
                    continue;
                }

                $regions[] = $school->getRegion()->getId();
            }
        }

        if ($loggedInUser->isProfessional()) {

            /** @var ProfessionalUser $loggedInUser */

            foreach ($loggedInUser->getRegions() as $region) {

                $regions[] = $region->getId();
            }
        }

        if ($loggedInUser->isStudent() || $loggedInUser->isEducator()) {

            /** @var StudentUser|EducatorUser $user */

            if ($loggedInUser->getSchool() && $loggedInUser->getSchool()->getRegion()) {
                $regions[] = $loggedInUser->getSchool()->getRegion()->getId();
            }
        }

        $regionIds = array_unique($regions);

        /**
         * START THE LOGIC FOR FINDING EXPERIENCES BY ZIPCODE
         */
        if ($zipcode && $coordinates = $this->geocoder->geocode($zipcode)) {
            $lng = $coordinates['lng'];
            $lat = $coordinates['lat'];
            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);
        }

        /** @var User $user */
        if ($schoolId && $school = $this->schoolRepository->find($schoolId)) {
            $schoolExperiences = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId, $startDate, $endDate, $searchQuery);
        } else {
            if ($userId) {
                /** @var User $user */
                $user            = $userId ? $this->userRepository->find($userId) : $this->getUser();
                $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUserByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $userId, $startDate, $endDate, $searchQuery, $eventType, $industry, $secondaryIndustry);
                
                // print_r($userExperiences);
                if ($user && $user->isStudent() && $user->getSchool()) {
                    $schoolId = $user->getSchool()->getId();
                    // get any school experiences that are part of your school
                    $schoolExperiences = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId, $startDate, $endDate, $searchQuery, $eventType, $industry, $secondaryIndustry);
                }
            } else {
                // Everyone sees all company events
                $schoolExperiences  = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId, $startDate, $endDate, $searchQuery, $eventType, $industry, $secondaryIndustry);
                $companyExperiences = $this->companyExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $startDate, $endDate, $searchQuery, $eventType, $industry, $secondaryIndustry, $regionIds);

                if ($loggedInUser->isSchoolAdministrator()) {
                    /** @var SchoolAdministrator $loggedInUser * */
                    // School Administrator will see all school events that they manage
                    foreach ($loggedInUser->getSchools() as $school) {
                        $schoolId          = $school->getId();
                        $experiences       = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId, $startDate, $endDate, $searchQuery, $eventType, $industry, $secondaryIndustry);
                        $schoolExperiences = array_unique(array_merge($schoolExperiences, $experiences), SORT_REGULAR);
                    }
                } else {
                    if ($loggedInUser->isEducator() || $loggedInUser->isStudent()) {
                        // Educator & students will see their school events
                        /** @var StudentUser|EducatorUser $loggedInUser * */
                        $school            = $loggedInUser->getSchool();
                        $schoolId          = $school->getId();
                        $schoolExperiences = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId, $startDate, $endDate, $searchQuery, $eventType, $industry, $secondaryIndustry);
                    } else {
                        if ($loggedInUser->isProfessional()) {
                            // Professional will see all school events that they VOLUNTEER AT
                            /** @var ProfessionalUser $loggedInUser * */
                            foreach ($loggedInUser->getSchools() as $school) {
                                $schoolId          = $school->getId();
                                $experiences       = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId, $startDate, $endDate, $searchQuery, $eventType, $industry, $secondaryIndustry);
                                $schoolExperiences = array_unique(array_merge($schoolExperiences, $experiences), SORT_REGULAR);
                            }
                        }
                    }
                }
            }
        }

        $experiences   = array_merge($schoolExperiences, $companyExperiences, $userExperiences);

        return new JsonResponse(
            [
                'success' => true,
                'data'    => $experiences,
                'filters' => $filters,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Example Request: http://pintex.test/api/experiences-by-radius?zipcode=54017
     *
     * @Route("/experiences-for-list-by-radius", name="get_experiences_for_list_by_radius", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getExperiencesForListByRadius(Request $request)
    {
        $loggedInUser       = $this->getUser();
        $companyExperiences = [];
        $schoolExperiences  = [];
        $userExperiences    = [];
        $userId             = $request->query->get('userId', null);
        $schoolId           = $request->query->get('schoolId', null);
        $zipcode            = $request->query->get('zipcode', null);
        $radius             = $request->query->get('radius', 70);
        $lng                = null;
        $lat                = null;

        /**
         * START THE LOGIC FOR FINDING EXPERIENCES BY ZIPCODE
         */
        if ($zipcode && $coordinates = $this->geocoder->geocode($zipcode)) {
            $lng = $coordinates['lng'];
            $lat = $coordinates['lat'];
            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);

            /** @var User $user */
            if ($schoolId && $school = $this->schoolRepository->find($schoolId)) {
                $schoolExperiences = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
            } else {
                if ($userId) {
                    /** @var User $user */
                    $user            = $userId ? $this->userRepository->find($userId) : $this->getUser();
                    $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUserByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $userId);
                    if ($user && $user->isStudent() && $user->getSchool()) {
                        $schoolId = $user->getSchool()->getId();
                        // get any school experiences that are part of your school
                        $schoolExperiences = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                    }
                } else {
                    // Everyone sees all company events
                    $schoolExperiences  = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng);
                    $companyExperiences = $this->companyExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng);

                    if ($loggedInUser->isSchoolAdministrator()) {
                        /** @var SchoolAdministrator $loggedInUser * */
                        // School Administrator will see all school events that they manage
                        foreach ($loggedInUser->getSchools() as $school) {
                            $schoolId          = $school->getId();
                            $experiences       = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                            $schoolExperiences = array_merge($schoolExperiences, $experiences);
                        }
                    } else {
                        if ($loggedInUser->isEducator() || $loggedInUser->isStudent()) {
                            // Educator & students will see their school events
                            /** @var StudentUser|EducatorUser $loggedInUser * */
                            $school            = $loggedInUser->getSchool();
                            $schoolId          = $school->getId();
                            $schoolExperiences = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                        } else {
                            if ($loggedInUser->isProfessional()) {
                                // Professional will see all school events that they VOLUNTEER AT
                                /** @var ProfessionalUser $loggedInUser * */
                                foreach ($loggedInUser->getSchools() as $school) {
                                    $schoolId          = $school->getId();
                                    $experiences       = $this->schoolExperienceRepository->search($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                                    $schoolExperiences = array_merge($schoolExperiences, $experiences);
                                }
                            }
                        }
                    }
                }
            }

            $experiences   = array_merge($schoolExperiences, $companyExperiences, $userExperiences);
            $experienceIds = array_map(
                function ($experience) {
                    return $experience['id'];
                }, $experiences
            );

            $experiences   = $this->experienceRepository->getEventsClosestToCurrentDateByArrayOfExperienceIds($experienceIds);
            $experienceIds = array_map(
                function ($experience) {
                    return $experience['id'];
                }, $experiences
            );

            $experiences = $this->experienceRepository->findBy(
                ['id' => $experienceIds], [
                                            'startDateAndTime' => 'ASC',
                                        ]
            );

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

                /** @var ProfessionalUser $user */

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
                $experiences = array_filter(
                    $experiences, function (Experience $experience) use ($regions) {

                    if ($experience instanceof CompanyExperience) {

                        if (!$experience->getCompany()) {
                            return false;
                        }

                        $hasMatch = false;
                        foreach ($experience->getCompany()->getRegions() as $region) {
                            if (in_array($region->getId(), $regions)) {
                                $hasMatch = true;
                            }
                        }

                        if (!$hasMatch) {
                            return false;
                        }

                    }

                    return true;
                }
                );
            }

            $experiences = array_values($experiences);

            $json    = $this->serializer->serialize(
                $experiences, 'json', [
                                'groups' => [
                                    'EXPERIENCE_DATA',
                                    'ALL_USER_DATA',
                                ],
                            ]
            );
            $payload = json_decode($json, true);

        } else {
            /**
             * START THE LOGIC FOR FINDING EXPERIENCES WITHOUT ZIPCODE
             */

            /** @var User $user */
            if ($schoolId && $school = $this->schoolRepository->find($schoolId)) {
                $schoolExperiences = $this->schoolExperienceRepository->findBy(
                    [
                        'school' => $school,
                    ]
                );
                // $companyExperiences = $this->companyExperienceRepository->getForSchool($school);
            } else {
                if ($userId) {
                    /** @var User $user */
                    $user            = $userId ? $this->userRepository->find($userId) : $this->getUser();
                    $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUser($user);
                } else {
                    // Everyone sees all company events
                    $companyExperiences = $this->companyExperienceRepository->findAll();

                    if ($loggedInUser->isSchoolAdministrator()) {
                        /** @var SchoolAdministrator $loggedInUser * */
                        // School Administrator will see all school events that they manage
                        foreach ($loggedInUser->getSchools() as $school) {
                            $experiences       = $this->schoolExperienceRepository->findBy(
                                [
                                    'school' => $school,
                                ]
                            );
                            $schoolExperiences = array_merge($schoolExperiences, $experiences);
                        }
                    } else {
                        if ($loggedInUser->isEducator() || $loggedInUser->isStudent()) {
                            // Educator & students will see their school events
                            /** @var StudentUser|EducatorUser $loggedInUser * */
                            $school            = $loggedInUser->getSchool();
                            $schoolExperiences = $this->schoolExperienceRepository->findBy(
                                [
                                    'school' => $school,
                                ]
                            );
                        } else {
                            if ($loggedInUser->isProfessional()) {
                                // Professional will see all school events that they VOLUNTEER AT
                                /** @var ProfessionalUser $loggedInUser * */
                                foreach ($loggedInUser->getSchools() as $school) {
                                    $experiences       = $this->schoolExperienceRepository->findBy(
                                        [
                                            'school' => $school,
                                        ]
                                    );
                                    $schoolExperiences = array_merge($schoolExperiences, $experiences);
                                }
                            }
                        }
                    }
                }
            }

            $experiences   = array_merge($schoolExperiences, $companyExperiences, $userExperiences);
            $experienceIds = array_map(
                function ($experience) {
                    return $experience->getId();
                }, $experiences
            );

            // we are only showing upcoming dates in the future for the list view
            $experiences   = $this->experienceRepository->getEventsClosestToCurrentDateByArrayOfExperienceIds($experienceIds);
            $experienceIds = array_map(
                function ($experience) {
                    return $experience['id'];
                }, $experiences
            );

            $experiences = $this->experienceRepository->findBy(
                ['id' => $experienceIds], [
                                            'startDateAndTime' => 'ASC',
                                        ]
            );

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

                /** @var ProfessionalUser $user */

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
                $experiences = array_filter(
                    $experiences, function (Experience $experience) use ($regions) {

                    if ($experience instanceof CompanyExperience) {

                        if (!$experience->getCompany()) {
                            return false;
                        }

                        $hasMatch = false;
                        foreach ($experience->getCompany()->getRegions() as $region) {
                            if (in_array($region->getId(), $regions)) {
                                $hasMatch = true;
                            }
                        }

                        if (!$hasMatch) {
                            return false;
                        }

                    }

                    return true;
                }
                );
            }

            $experiences = array_values($experiences);

            $json    = $this->serializer->serialize(
                $experiences, 'json', [
                                'groups' => [
                                    'EXPERIENCE_DATA',
                                    'ALL_USER_DATA',
                                ],
                            ]
            );
            $payload = json_decode($json, true);
        }

        return new JsonResponse(
            [
                'success' => true,
                'data'    => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/experiences/{id}/remove", name="remove_experience", methods={"POST"}, options = { "expose" = true })
     * @param Experience $experience
     * @param Request    $request
     *
     * @return JsonResponse
     */
    public function removeExperience(Experience $experience, Request $request)
    {

        $this->denyAccessUnlessGranted('edit', $experience);

        $this->entityManager->remove($experience);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/share/notify", name="share_notify", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function experienceNotifyUsersAction(Request $request)
    {

        $loggedInUser = $this->getUser();
        if( $loggedInUser->isStudent() ){
            
        } else {
            $systemUser = $loggedInUser;
        }

        if(!$systemUser) {
            $systemUser = $this->systemUserRepository->findOneBy(
                [
                    'type' => SystemUser::EXPERIENCE_NOTIFY,
                ]
            );
        }

        if (!$systemUser) {
            $systemUser = new SystemUser();
            $systemUser->setFirstName('Experience');
            $systemUser->setLastName('Notification');
            $systemUser->setType(SystemUser::EXPERIENCE_NOTIFY);
            $this->entityManager->persist($systemUser);
            $this->entityManager->flush();
        }


        $userIds = $request->request->get('user_ids');

        if (empty($userIds)) {
            return $this->json(
                [
                    'message' => 'You must select at least one user to notify',
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $customMessage = $request->request->get('message', '');

        $users = $this->userRepository->findBy(
            [
                'id' => $userIds,
            ]
        );

        /** @var User $user */
        foreach ($users as $user) {

            $chat = $this->chatRepository->findOneBy(
                [
                    'userOne' => $systemUser,
                    'userTwo' => $user,
                ]
            );

            if (!$chat) {
                $chat = $this->chatRepository->findOneBy(
                    [
                        'userOne' => $user,
                        'userTwo' => $systemUser,
                    ]
                );
            }

            // if a chat doesn't exist then let's create one!
            if (!$chat) {
                $chat = new Chat();
                $chat->setUserOne($user);
                $chat->setUserTwo($systemUser);
                $this->entityManager->persist($chat);
                $this->entityManager->flush();
            }


            $notice = $customMessage;

            $chatMessage = new ChatMessage();
            $chatMessage->setBody($notice);
            $chatMessage->setSentFrom($systemUser);
            $chatMessage->setSentAt(new \DateTime());
            $chatMessage->setChat($chat);

            // Figure out which user to message from the chat object
            $userToMessage = $chat->getUserOne()->getId() === $systemUser->getId() ? $chat->getUserTwo() : $chat->getUserOne();
            $chatMessage->setSentTo($userToMessage);

            $this->entityManager->persist($chatMessage);
            $this->entityManager->flush();

            $this->experienceMailer->genericShareNotification($customMessage, $user, $loggedInUser);
        }

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'Notifications successfully sent out.',
            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/experiences/{id}/teach-lesson-event-change-date", name="experience_teach_lesson_event_change_date", options = { "expose" = true }, methods={"POST"})
     * @param Request               $request
     * @param TeachLessonExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function experienceTeachLessonEventChangeDateAction(Request $request, TeachLessonExperience $experience)
    {

        /** @var User $user */
        $user = $this->getUser();

        $newStartDate = $request->request->get('newStartDate');
        $newStartDate = DateTime::createFromFormat('m/d/Y g:i A', $newStartDate);

        $newEndDate = $request->request->get('newEndDate');
        $newEndDate = DateTime::createFromFormat('m/d/Y g:i A', $newEndDate);

        $customMessage = $request->request->get('customMessage');

        $experience->setStartDateAndTime($newStartDate);
        $experience->setEndDateAndTime($newEndDate);
        $this->entityManager->persist($experience);
        $this->entityManager->flush();

        if ($experience->getTeacher()) {
            $this->experienceMailer->notifyUserOfEventDateChange($experience, $experience->getTeacher(), $customMessage);
        }

        if ($user->getEmail()) {
            $this->experienceMailer->notifyUserOfEventDateChange($experience, $user, $customMessage);
        }

        $this->addFlash('success', 'Date successfully changed. Professional will be notified.');

        return $this->redirectToRoute('requests');
    }

    /**
     * @Route("/experiences/{id}/teach_lesson_event_delete", name="experience_teach_lesson_event_delete", options = { "expose" = true }, methods={"POST"}, requirements={"id": "\d+"})
     * @param Request               $request
     * @param TeachLessonExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceTeachLessonEventDeleteAction(Request $request, TeachLessonExperience $experience)
    {
        /** @var User $user */
        $user = $this->getUser();

        $customMessage = $request->request->get('customMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {

            if ($registration->getUser()->isStudent()) {
                continue;
            }

            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $customMessage);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach ($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully cancelled. Users will be notified.');

        return $this->redirectToRoute('requests');
    }


    /**
     * @Route("/experiences/{id}/company_event_delete", name="experience_company_event_delete", options = { "expose" = true }, methods={"POST"}, requirements={"id": "\d+"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceCompanyEventDeleteAction(Request $request, CompanyExperience $experience)
    {

        /** @var User $user */
        $user = $this->getUser();

        $customMessage = $request->request->get('customMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {

            if ($registration->getUser()->isStudent()) {
                continue;
            }

            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $customMessage);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach ($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully cancelled. Users will be notified.');

        return $this->redirectToRoute('dashboard');
    }


    /**
     * @Route("/experiences/{id}/school_event_delete", name="experience_school_event_delete", options = { "expose" = true }, methods={"POST"}, requirements={"id": "\d+"})
     * @param Request          $request
     * @param SchoolExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceSchoolEventDeleteAction(Request $request, SchoolExperience $experience)
    {

        /** @var User $user */
        $user = $this->getUser();

        $customMessage = $request->request->get('customMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {

            if ($registration->getUser()->isStudent()) {
                continue;
            }

            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $customMessage);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach ($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully cancelled. Users will be notified.');

        return $this->redirectToRoute('dashboard');
    }



    /**
     * Example Request: http://pintex.test/api/experiences-by-user?user
     *
     * @Route("/experiences-by-user", name="get_experiences_by_user", methods={"GET"}, options = { "expose" = true })
     * @param Request         $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getExperiencesByUser(Request $request)
    {
        $user               = $this->getUser();
        $experiences = [];
       
        $allMyExperiences = $this->experienceRepository->getAllEventsRegisteredForByUser($user);
        foreach($allMyExperiences as $r) {
            $url = "";
            $requestId = "";
            $className = $r->getClassName();
            $street = "";
            $city = "";
            $abbreviation = "";
            $zipcode = "";

            if($className == 'TeachLessonExperience') {
                /** @var TeachLessonExperience $r */
                $url = $this->generateUrl('lesson_view', ['id' => $r->getLesson()->getId()]);
                $requestId = $r->getOriginalRequest()->getId();

                if($r->getSchool() && $r->getSchool()->getStreet()) {
                    $street = $r->getSchool()->getStreet();
                }

                if($r->getSchool() && $r->getSchool()->getCity()) {
                    $city = $r->getSchool()->getCity();
                }

                if($r->getSchool() && $r->getSchool()->getState() && $r->getSchool()->getState()->getAbbreviation()) {
                    $abbreviation = $r->getSchool()->getState()->getAbbreviation();
                }

                if($r->getSchool() && $r->getSchool()->getZipcode()) {
                    $zipcode = $r->getSchool()->getZipcode();
                }

            }

            $experiences[] = array(
                "id" => $r->getId(),
                "requestId" => $requestId,
                "title" => $r->getTitle(),
                "about" => $r->getAbout(),
                "briefDescription" => $r->getBriefDescription(),
                "startDateAndTimeTimestamp" => $r->getStartDateAndTimeTimeStamp(),
                "endDateAndTimeTimestamp" => $r->getEndDateAndTimeTimeStamp(),
                "startDateAndTime" => $r->getStartDateAndTime()->format('Y-m-d H:i:s'),
                "endDateAndTime" => $r->getEndDateAndTime()->format("Y-m-d H:i:s"),
                "className" => $className,
                "url" => $url,
                "street" => $street,
                "city" => $city,
                "zipcode" => $zipcode,
                "state" => [
                    "abbreviation" => $abbreviation
                ]
            );
        }

        return new JsonResponse(
            [
                'success' => true,
                'data'    => $experiences
            ],
            Response::HTTP_OK
        );
    }
}
