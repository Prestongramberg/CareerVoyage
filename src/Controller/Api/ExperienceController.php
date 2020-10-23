<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonFavorite;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyRepository;
use App\Repository\ExperienceRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\SchoolExperienceRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
 * Class ExperienceController
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
     * @return JsonResponse
     */
    public function getExperiences(Request $request) {
        $loggedInUser = $this->getUser();
        $companyExperiences = [];
        $schoolExperiences = [];
        $userExperiences = [];
        $userId = $request->query->get('userId', null);
        $schoolId = $request->query->get('schoolId', null);
        /** @var User $user */
        if($schoolId && $school = $this->schoolRepository->find($schoolId)) {
            $schoolExperiences = $this->schoolExperienceRepository->findBy([
                'school' => $school
            ]);
            // $companyExperiences = $this->companyExperienceRepository->getForSchool($school);
        } else if ( $userId ) {
            /** @var User $user */
            $user = $userId ? $this->userRepository->find($userId) : $this->getUser();
            $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUser($user);
            if($user && $user->isStudent() && $user->getSchool()) {
                // get any school experiences that are part of your school
                $schoolExperiences = $this->schoolExperienceRepository->findBy([
                    'school' => $user->getSchool()
                ]);
            }
        } else {
        	// Everyone sees all company events
	        $companyExperiences = $this->companyExperienceRepository->findAll();

	        if ( $loggedInUser->isSchoolAdministrator() ) {
		        /** @var SchoolAdministrator $loggedInUser **/
		        // School Administrator will see all school events that they manage
		        foreach($loggedInUser->getSchools() as $school) {
			        $experiences = $this->schoolExperienceRepository->findBy([
				        'school' => $school
			        ]);
			        $schoolExperiences = array_merge($schoolExperiences, $experiences);
		        }
	        } else if ( $loggedInUser->isEducator() || $loggedInUser->isStudent() ) {
		        // Educator & students will see their school events
		        /** @var StudentUser|EducatorUser $loggedInUser **/
	        	$school = $loggedInUser->getSchool();
		        $schoolExperiences = $this->schoolExperienceRepository->findBy([
			        'school' => $school
		        ]);
	        } else if ( $loggedInUser->isProfessional() ) {
		        // Professional will see all school events that they VOLUNTEER AT
		        /** @var ProfessionalUser $loggedInUser **/
		        foreach($loggedInUser->getSchools() as $school) {
			        $experiences = $this->schoolExperienceRepository->findBy([
				        'school' => $school
			        ]);
			        $schoolExperiences = array_merge($schoolExperiences, $experiences);
		        }
	        }
        }

        $experiences = array_merge($schoolExperiences, $companyExperiences, $userExperiences);

        $json = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA', 'ALL_USER_DATA']]);
        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Example Request: http://pintex.test/api/experiences-by-radius?zipcode=54017
     *
     * @Route("/experiences-by-radius", name="get_experiences_by_radius", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getExperiencesByRadius(Request $request) {
        $loggedInUser = $this->getUser();
        $companyExperiences = [];
        $schoolExperiences = [];
        $userExperiences = [];
        $userId = $request->query->get('userId', null);
        $schoolId = $request->query->get('schoolId', null);
        $zipcode = $request->query->get('zipcode',  null);
        $radius = $request->query->get('radius', 70);
        $lng = null;
        $lat = null;

        /**
         * START THE LOGIC FOR FINDING EXPERIENCES BY ZIPCODE
         */
        if($zipcode &&  $coordinates = $this->geocoder->geocode($zipcode)) {
            $lng = $coordinates['lng'];
            $lat = $coordinates['lat'];
            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);

            /** @var User $user */
            if($schoolId && $school = $this->schoolRepository->find($schoolId)) {
                $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
            } else if ( $userId ) {
                /** @var User $user */
                $user = $userId ? $this->userRepository->find($userId) : $this->getUser();
                $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUserByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $userId);
                if($user && $user->isStudent() && $user->getSchool()) {
                    $schoolId = $user->getSchool()->getId();
                    // get any school experiences that are part of your school
                    $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                }
            } else {
                // Everyone sees all company events
                $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
                $companyExperiences = $this->companyExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);

                if ( $loggedInUser->isSchoolAdministrator() ) {
                    /** @var SchoolAdministrator $loggedInUser **/
                    // School Administrator will see all school events that they manage
                    foreach($loggedInUser->getSchools() as $school) {
                        $schoolId = $school->getId();
                        $experiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                } else if ( $loggedInUser->isEducator() || $loggedInUser->isStudent() ) {
                    // Educator & students will see their school events
                    /** @var StudentUser|EducatorUser $loggedInUser **/
                    $school = $loggedInUser->getSchool();
                    $schoolId = $school->getId();
                    $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                } else if ( $loggedInUser->isProfessional() ) {
                    // Professional will see all school events that they VOLUNTEER AT
                    /** @var ProfessionalUser $loggedInUser **/
                    foreach($loggedInUser->getSchools() as $school) {
                        $schoolId = $school->getId();
                        $experiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                }
            }

            $experiences = array_merge($schoolExperiences, $companyExperiences, $userExperiences);
            $experienceIds = array_map(function($experience) { return $experience['id']; }, $experiences);
            $experiences = $this->experienceRepository->findBy(['id' => $experienceIds]);
	        $json = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA', 'ALL_USER_DATA']]);
	        $payload = json_decode($json, true);

        } else {
            /**
             * START THE LOGIC FOR FINDING EXPERIENCES WITHOUT ZIPCODE
             */

            /** @var User $user */
            if($schoolId && $school = $this->schoolRepository->find($schoolId)) {
                $schoolExperiences = $this->schoolExperienceRepository->findBy([
                    'school' => $school
                ]);
                // $companyExperiences = $this->companyExperienceRepository->getForSchool($school);
            } else if ( $userId ) {
                /** @var User $user */
                $user = $userId ? $this->userRepository->find($userId) : $this->getUser();
                $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUser($user);
            } else {
                // Everyone sees all company events
                $companyExperiences = $this->companyExperienceRepository->findBy(['cancelled' => 0]);

                if ( $loggedInUser->isSchoolAdministrator() ) {
                    /** @var SchoolAdministrator $loggedInUser **/
                    // School Administrator will see all school events that they manage
                    foreach($loggedInUser->getSchools() as $school) {
                        $experiences = $this->schoolExperienceRepository->findBy([
                            'school' => $school
                        ]);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                } else if ( $loggedInUser->isEducator() || $loggedInUser->isStudent() ) {
                    // Educator & students will see their school events
                    /** @var StudentUser|EducatorUser $loggedInUser **/
                    $school = $loggedInUser->getSchool();
                    $schoolExperiences = $this->schoolExperienceRepository->findBy([
                        'school' => $school
                    ]);
                } else if ( $loggedInUser->isProfessional() ) {
                    // Professional will see all school events that they VOLUNTEER AT
                    /** @var ProfessionalUser $loggedInUser **/
                    foreach($loggedInUser->getSchools() as $school) {
                        $experiences = $this->schoolExperienceRepository->findBy([
                            'school' => $school
                        ]);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                }
            }

            $experiences = array_merge($schoolExperiences, $companyExperiences, $userExperiences);
            $json = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA', 'ALL_USER_DATA']]);
            $payload = json_decode($json, true);
        }

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Example Request: http://pintex.test/api/experiences-by-radius?zipcode=54017
     *
     * @Route("/experiences-for-list-by-radius", name="get_experiences_for_list_by_radius", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getExperiencesForListByRadius(Request $request) {
        $loggedInUser = $this->getUser();
        $companyExperiences = [];
        $schoolExperiences = [];
        $userExperiences = [];
        $userId = $request->query->get('userId', null);
        $schoolId = $request->query->get('schoolId', null);
        $zipcode = $request->query->get('zipcode',  null);
        $radius = $request->query->get('radius', 70);
        $lng = null;
        $lat = null;

        /**
         * START THE LOGIC FOR FINDING EXPERIENCES BY ZIPCODE
         */
        if($zipcode &&  $coordinates = $this->geocoder->geocode($zipcode)) {
            $lng = $coordinates['lng'];
            $lat = $coordinates['lat'];
            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);

            /** @var User $user */
            if($schoolId && $school = $this->schoolRepository->find($schoolId)) {
                $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
            } else if ( $userId ) {
                /** @var User $user */
                $user = $userId ? $this->userRepository->find($userId) : $this->getUser();
                $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUserByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $userId);
                if($user && $user->isStudent() && $user->getSchool()) {
                    $schoolId = $user->getSchool()->getId();
                    // get any school experiences that are part of your school
                    $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                }
            } else {
                // Everyone sees all company events
                $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
                $companyExperiences = $this->companyExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);

                if ( $loggedInUser->isSchoolAdministrator() ) {
                    /** @var SchoolAdministrator $loggedInUser **/
                    // School Administrator will see all school events that they manage
                    foreach($loggedInUser->getSchools() as $school) {
                        $schoolId = $school->getId();
                        $experiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                } else if ( $loggedInUser->isEducator() || $loggedInUser->isStudent() ) {
                    // Educator & students will see their school events
                    /** @var StudentUser|EducatorUser $loggedInUser **/
                    $school = $loggedInUser->getSchool();
                    $schoolId = $school->getId();
                    $schoolExperiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                } else if ( $loggedInUser->isProfessional() ) {
                    // Professional will see all school events that they VOLUNTEER AT
                    /** @var ProfessionalUser $loggedInUser **/
                    foreach($loggedInUser->getSchools() as $school) {
                        $schoolId = $school->getId();
                        $experiences = $this->schoolExperienceRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng, $schoolId);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                }
            }

            $experiences = array_merge($schoolExperiences, $companyExperiences, $userExperiences);
            $experienceIds = array_map(function($experience) { return $experience['id']; }, $experiences);

            $experiences = $this->experienceRepository->getEventsClosestToCurrentDateByArrayOfExperienceIds($experienceIds);
            $experienceIds = array_map(function($experience) { return $experience['id']; }, $experiences);

            $experiences = $this->experienceRepository->findBy(['id' => $experienceIds], [
                'startDateAndTime' => 'ASC'
            ]);

            $json = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA', 'ALL_USER_DATA']]);
            $payload = json_decode($json, true);

        } else {
            /**
             * START THE LOGIC FOR FINDING EXPERIENCES WITHOUT ZIPCODE
             */

            /** @var User $user */
            if($schoolId && $school = $this->schoolRepository->find($schoolId)) {
                $schoolExperiences = $this->schoolExperienceRepository->findBy([
                    'school' => $school
                ]);
                // $companyExperiences = $this->companyExperienceRepository->getForSchool($school);
            } else if ( $userId ) {
                /** @var User $user */
                $user = $userId ? $this->userRepository->find($userId) : $this->getUser();
                $userExperiences = $this->experienceRepository->getAllEventsRegisteredForByUser($user);
            } else {
                // Everyone sees all company events
                $companyExperiences = $this->companyExperienceRepository->findAll();

                if ( $loggedInUser->isSchoolAdministrator() ) {
                    /** @var SchoolAdministrator $loggedInUser **/
                    // School Administrator will see all school events that they manage
                    foreach($loggedInUser->getSchools() as $school) {
                        $experiences = $this->schoolExperienceRepository->findBy([
                            'school' => $school
                        ]);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                } else if ( $loggedInUser->isEducator() || $loggedInUser->isStudent() ) {
                    // Educator & students will see their school events
                    /** @var StudentUser|EducatorUser $loggedInUser **/
                    $school = $loggedInUser->getSchool();
                    $schoolExperiences = $this->schoolExperienceRepository->findBy([
                        'school' => $school
                    ]);
                } else if ( $loggedInUser->isProfessional() ) {
                    // Professional will see all school events that they VOLUNTEER AT
                    /** @var ProfessionalUser $loggedInUser **/
                    foreach($loggedInUser->getSchools() as $school) {
                        $experiences = $this->schoolExperienceRepository->findBy([
                            'school' => $school
                        ]);
                        $schoolExperiences = array_merge($schoolExperiences, $experiences);
                    }
                }
            }

            $experiences = array_merge($schoolExperiences, $companyExperiences, $userExperiences);
            $experienceIds = array_map(function($experience) { return $experience->getId(); }, $experiences);

            // we are only showing upcoming dates in the future for the list view
            $experiences = $this->experienceRepository->getEventsClosestToCurrentDateByArrayOfExperienceIds($experienceIds);
            $experienceIds = array_map(function($experience) { return $experience['id']; }, $experiences);

            $experiences = $this->experienceRepository->findBy(['id' => $experienceIds], [
                'startDateAndTime' => 'ASC'
            ]);

            $json = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA', 'ALL_USER_DATA']]);
            $payload = json_decode($json, true);
        }

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/experiences/{id}/remove", name="remove_experience", methods={"POST"}, options = { "expose" = true })
     * @param Experience $experience
     * @param Request $request
     * @return JsonResponse
     */
    public function removeExperience(Experience $experience, Request $request) {

        $this->denyAccessUnlessGranted('edit', $experience);

        $this->entityManager->remove($experience);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/share/notify", name="share_notify", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function experienceNotifyUsersAction(Request $request) {

        $loggedInUser = $this->getUser();

        $userIds = $request->request->get('user_ids');

        if(empty($userIds)) {
            return $this->json([
                'message' => 'You must select at least one user to notify'
            ], Response::HTTP_BAD_REQUEST);
        }

        $customMessage = $request->request->get('message', '');

        $users = $this->userRepository->findBy([
            'id' => $userIds
        ]);

        /** @var User $user */
        foreach($users as $user) {

            $chat = $this->chatRepository->findOneBy([
                'userOne' => $loggedInUser,
                'userTwo' => $user
            ]);

            if(!$chat) {
                $chat = $this->chatRepository->findOneBy([
                    'userOne' => $user,
                    'userTwo' => $loggedInUser
                ]);
            }

            // if a chat doesn't exist then let's create one!
            if(!$chat) {
                $chat = new Chat();
                $chat->setUserOne($user);
                $chat->setUserTwo($loggedInUser);
                $this->entityManager->persist($chat);
                $this->entityManager->flush();
            }


            $notice = $customMessage;

            $chatMessage = new ChatMessage();
            $chatMessage->setBody($notice);
            $chatMessage->setSentFrom($loggedInUser);
            $chatMessage->setSentAt(new \DateTime());
            $chatMessage->setChat($chat);

            // Figure out which user to message from the chat object
            $userToMessage = $chat->getUserOne()->getId() === $loggedInUser->getId() ? $chat->getUserTwo() : $chat->getUserOne();
            $chatMessage->setSentTo($userToMessage);

            $this->entityManager->persist($chatMessage);
            $this->entityManager->flush();

            $this->experienceMailer->genericShareNotification($customMessage, $user);
        }

        return new JsonResponse(
        	[
        		'success' => true,
        		'message' => 'Notifications successfully sent out.'
	        ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/experiences/{id}/teach-lesson-event-change-date", name="experience_teach_lesson_event_change_date", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param TeachLessonExperience $experience
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function experienceTeachLessonEventChangeDateAction(Request $request, TeachLessonExperience $experience) {

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

        if($experience->getTeacher()) {
            $this->experienceMailer->notifyUserOfEventDateChange($experience, $experience->getTeacher(), $customMessage);
        }

        if($user->getEmail()) {
            $this->experienceMailer->notifyUserOfEventDateChange($experience, $user, $customMessage);
        }

        $this->addFlash('success', 'Date successfully changed. Professional will be notified.');

        return $this->redirectToRoute('requests');
    }


    // Request New Dates, this function will cancel the original request and create a new one.
    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * 
     * @Route("/experiences/{id}/teach-lesson-event-request-new-dates", name="experience_teach_lesson_event_request_new_dates", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceTeachLessonEventRequestNewDatesAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $experience = $this->experienceRepository->find( $request->get('id') );
        $professionalUser = $experience->getOriginalRequest()->getNeedsApprovalBy();
        $lesson = $experience->getOriginalRequest()->getLesson();
        $origin_request_id = $experience->getOriginalRequest()->getId();

        // Cancel existing experience
        $exp = $this->entityManager->getRepository(Experience::class)->find( $request->get('id') );
        $exp->setCancelled(true);
        $this->entityManager->flush();

        // Change status from approved to denied on origin request. (need to call request class this way becuase we are importing another class named "Request" above)
        $exp2 = $this->entityManager->getRepository('App\\Entity\\Request')->find( $origin_request_id );
        $exp2->setApproved(false);
        $exp2->setDenied(true);
        $this->entityManager->flush();

        // Create new experience request
        $dateOptionOne = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('date_option_one'));
        $dateOptionTwo = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('date_option_two'));
        $dateOptionThree = DateTime::createFromFormat('m/d/Y g:i A', $request->request->get('date_option_three'));
        $teachLessonRequest = new TeachLessonRequest();
        $teachLessonRequest->setDateOptionOne($dateOptionOne);
        $teachLessonRequest->setDateOptionTwo($dateOptionTwo);
        $teachLessonRequest->setDateOptionThree($dateOptionThree);
        $teachLessonRequest->setLesson($lesson);
        $teachLessonRequest->setCreatedBy($user);
        $teachLessonRequest->setNeedsApprovalBy($professionalUser);
        $teachLessonRequest->setSchool($user->getSchool());
        $teachLessonRequest->setMessage( $request->request->get('customMessage'));
        $this->entityManager->persist($teachLessonRequest);
        $this->entityManager->flush();

        $this->requestsMailer->teachLessonRequest($teachLessonRequest);

        $this->addFlash('success', 'Request successfully sent!');

     /*   if($redirectUrl) {
            return $this->redirect($redirectUrl);
        }*/

        return $this->redirectToRoute('requests');
    }








    /**
     * @Route("/experiences/{id}/teach_lesson_event_delete", name="experience_teach_lesson_event_delete", options = { "expose" = true }, methods={"POST"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param TeachLessonExperience $experience
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceTeachLessonEventDeleteAction(Request $request, TeachLessonExperience $experience) {


        var_dump($request);
        die();


        /** @var User $user */
        $user = $this->getUser();

        $customMessage = $request->request->get('customMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {

            if($registration->getUser()->isStudent()) {
                continue;
            }

            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $customMessage);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully cancelled. Users will be notified.');

        return $this->redirectToRoute('requests');
    }


    /**
     * @Route("/experiences/{id}/company_event_delete", name="experience_company_event_delete", options = { "expose" = true }, methods={"POST"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param CompanyExperience $experience
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceCompanyEventDeleteAction(Request $request, CompanyExperience $experience) {

        /** @var User $user */
        $user = $this->getUser();

        $customMessage = $request->request->get('customMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {

            if($registration->getUser()->isStudent()) {
                continue;
            }

            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $customMessage);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully cancelled. Users will be notified.');

        return $this->redirectToRoute('dashboard');
    }


    /**
     * @Route("/experiences/{id}/school_event_delete", name="experience_school_event_delete", options = { "expose" = true }, methods={"POST"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param SchoolExperience $experience
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceSchoolEventDeleteAction(Request $request, SchoolExperience $experience) {

        /** @var User $user */
        $user = $this->getUser();

        $customMessage = $request->request->get('customMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {

            if($registration->getUser()->isStudent()) {
                continue;
            }

            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $customMessage);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully cancelled. Users will be notified.');

        return $this->redirectToRoute('dashboard');
    }
}
