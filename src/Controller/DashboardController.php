<?php

namespace App\Controller;

use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Experience;
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
use Doctrine\Common\Collections\ArrayCollection;
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
     * @param  Request           $request
     * @param  SessionInterface  $session
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
            'upcomingEventsRegisteredForByUser'                 => $upcomingEventsRegisteredForByUser,
            'completedEventsRegisteredForByUser'                => $completedEventsRegisteredForByUser,
            'completedEventsRegisteredForByUserMissingFeedback' => $completedEventsRegisteredForByUserMissingFeedback,
            'completedFeedback'                                 => $completedFeedback,
            'user'                                              => $user,
            'sites'                                             => $sites,
            'schoolId'                                          => $request->query->has('school') ? (int)$request->query->get('school') : null,
            'authorizationVoter'                                => new AuthorizationVoter(),
        ]);
    }


    public function myRequests(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $requestsThatNeedMyApproval = $this->requestRepository->getRequestsThatNeedMyApproval($user, true);

        return $this->render('dashboard/my_requests.html.twig', [
            'user'                       => $user,
            'requestsThatNeedMyApproval' => $requestsThatNeedMyApproval,
        ]);
    }

    public function experiences(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $experienceIds = [];

        $schoolExperiences = $this->schoolExperienceRepository->createQueryBuilder('e')
            ->andWhere('e.cancelled = :cancelled')
            ->andWhere('e.isRecurring = :isRecurring')
            ->andWhere('e.startDateAndTime >= :today')
            ->setParameter('cancelled', false)
            ->setParameter('isRecurring', false)
            ->setParameter('today', new \DateTime())
            ->orderBy('e.startDateAndTime', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        $companyExperiences = $this->companyExperienceRepository->createQueryBuilder('e')
            ->andWhere('e.cancelled = :cancelled')
            ->andWhere('e.isRecurring = :isRecurring')
            ->andWhere('e.startDateAndTime >= :today')
            ->setParameter('cancelled', false)
            ->setParameter('isRecurring', false)
            ->setParameter('today', new \DateTime())
            ->orderBy('e.startDateAndTime', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        foreach ($schoolExperiences as $schoolExperience) {
            $experienceIds[] = $schoolExperience->getId();
        }

        foreach ($companyExperiences as $companyExperience) {
            $experienceIds[] = $companyExperience->getId();
        }

        $experiences = $this->experienceRepository->createQueryBuilder('e')
            ->andWhere('e.id in (:experienceIds)')
            ->setParameter('experienceIds', $experienceIds)
            ->orderBy('e.startDateAndTime', 'ASC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();

     /*   $experiences = new ArrayCollection($experiences);

        $experiences = $experiences->filter(function (Experience $experience) {
            return $experience->getStartDateAndTime() > new \DateTime();
        });*/


        return $this->render('dashboard/experiences.html.twig', [
            'user'        => $user,
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
            'title'     => 'ASC',
        ], 50);

        return $this->render('dashboard/topics.html.twig', [
            'user'    => $user,
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
                'lastName'  => 'ASC',
            ], 50);
        }

        return $this->render('dashboard/volunteer_professionals.html.twig', [
            'user'                   => $user,
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

        $requestEntities = array_filter($requestEntities, function (RequestEntity $requestEntity) use ($hiddenRequestIds) {
            return !in_array($requestEntity->getId(), $hiddenRequestIds, true);
        });

        return $this->render('dashboard/job_board.html.twig', [
            'user'            => $user,
            'requestEntities' => $requestEntities,
        ]);
    }

}
