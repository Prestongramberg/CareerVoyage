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
use App\Util\AuthorizationVoter;
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
        $user = $this->getUser();

        $upcomingEventsRegisteredForByUser                 = $this->experienceRepository->getUpcomingEventsRegisteredForByUser($user);
        $completedEventsRegisteredForByUser                = $this->experienceRepository->getCompletedEventsRegisteredForByUser($user);
        $completedEventsRegisteredForByUserMissingFeedback = $this->experienceRepository->getCompletedEventsRegisteredForByUserMissingFeedback($user);
        $completedFeedback                                 = $this->feedbackRepository->getForUser($user);
        $sites                                             = $this->siteRepository->findAll();

        return $this->render('dashboard/index.html.twig', [
            'upcomingEventsRegisteredForByUser' => $upcomingEventsRegisteredForByUser,
            'completedEventsRegisteredForByUser' => $completedEventsRegisteredForByUser,
            'completedEventsRegisteredForByUserMissingFeedback' => $completedEventsRegisteredForByUserMissingFeedback,
            'completedFeedback' => $completedFeedback,
            'user' => $user,
            'sites' => $sites,
            'schoolId' => $request->query->has('school') ? (int)$request->query->get('school') : null,
            'authorizationVoter' => new AuthorizationVoter(),
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

        $experienceIds = [];

        if ($user->isStudent() && $user->getSchool()) {
            $schoolExperiences = $this->schoolExperienceRepository->findBy(['cancelled' => false,
                                                                            'school' => $user->getSchool(),
            ], [
                'createdAt' => 'DESC',
            ], 50);
        } else {
            $schoolExperiences = $this->schoolExperienceRepository->findBy(['cancelled' => false], [
                'createdAt' => 'DESC',
            ], 50);
        }

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

        $requestEntities = $this->requestRepository->getRequestsThatNeedMyApproval($user, true, \App\Entity\Request::REQUEST_TYPE_JOB_BOARD);

        $hiddenRequests = $this->userMetaRepository->findBy([
            'user' => $user,
            'name' => UserMeta::HIDE_REQUEST,
        ]);

        $hiddenRequestIds = array_map(function (UserMeta $hiddenRequest) {
            return (int)$hiddenRequest->getValue();
        }, $hiddenRequests);

        $requestEntities = array_filter($requestEntities, function (RequestEntity $requestEntity) use ($hiddenRequestIds
        ) {
            return !in_array($requestEntity->getId(), $hiddenRequestIds, true);
        });

        return $this->render('dashboard/job_board.html.twig', [
            'user' => $user,
            'requestEntities' => $requestEntities,
        ]);
    }
}
