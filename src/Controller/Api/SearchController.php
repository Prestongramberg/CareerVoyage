<?php

namespace App\Controller\Api;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\SearchFilterType;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Request as RequestEntity;

/**
 * Class ManageUsersController
 *
 * @package App\Controller
 * @Route("/api/search")
 */
class SearchController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/", name="manage_users", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('manageUsers/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/users", name="search_users", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction(Request $request)
    {
        /** @var User $user */
        $user                = $loggedInUser = $this->getUser();
        $regionIds           = [];
        $schoolIds           = [];
        $experienceId        = $request->query->get('experience', null);
        $requestId           = $request->query->get('request', null);
        $experience          = null;
        $requestEntity       = null;
        $filters             = $request->request->get('filters', []);
        $userRole            = $filters['userRole'] ?? null;
        $serializationGroups = ['ALL_USER_DATA', 'STUDENT_USER', 'EDUCATOR_USER_DATA', 'PROFESSIONAL_USER_DATA'];

        if ($experienceId) {
            $experience = $this->experienceRepository->find($experienceId);
        }

        if ($requestId) {
            $requestEntity = $this->requestRepository->find($requestId);
        }

        /**
         * @var StudentUser $loggedInUser
         */
        if ($loggedInUser->isStudent()) {
            if ($loggedInUser->getSchool()) {
                $schoolIds[] = $loggedInUser->getSchool()->getId();
            }
        }

        /**
         * Professionals can message
         * 1. All educators on the platform
         * 2. All school administrators
         * 4. All Professional Users
         *
         * @var ProfessionalUser $loggedInUser
         */
        if ($loggedInUser->isProfessional()) {
            // noop
        }

        /**
         * @var EducatorUser $loggedInUser
         */
        if ($loggedInUser->isEducator()) {
            if ($loggedInUser->getSchool()) {
                $schoolIds[] = $loggedInUser->getSchool()->getId();
            }
        }

        /**
         * @var SchoolAdministrator $loggedInUser
         */
        if ($loggedInUser->isSchoolAdministrator()) {
            foreach ($loggedInUser->getSchools() as $school) {
                $schoolIds[] = $school->getId();
            }
        }

        if($loggedInUser instanceof StudentUser || $loggedInUser instanceof ProfessionalUser) {
            $filterBuilder = $this->userRepository->search($schoolIds, true, true);
        } else {
            $filterBuilder = $this->userRepository->search($schoolIds, true);
        }

        $form = $this->createForm(SearchFilterType::class, null, [
            'method'        => 'GET',
            'userRole'      => $userRole,
            'requestEntity' => $requestEntity,
            'schoolIds'     => $schoolIds,
            'loggedInUser'  => $loggedInUser,
        ]);

        $form->submit($request->request->get('filters'));

        $userRole = $form->get('userRole')->getData();

        if ($userRole === User::ROLE_PROFESSIONAL_USER) {
            $serializationGroups = ['ALL_USER_DATA', 'PROFESSIONAL_USER_DATA'];
        } elseif ($userRole === User::ROLE_EDUCATOR_USER) {
            $serializationGroups = ['ALL_USER_DATA', 'EDUCATOR_USER_DATA'];
        } elseif ($userRole === User::ROLE_STUDENT_USER) {
            $serializationGroups = ['ALL_USER_DATA', 'STUDENT_USER'];
        } elseif ($userRole === User::ROLE_SCHOOL_ADMINISTRATOR_USER) {
            $serializationGroups = ['ALL_USER_DATA', 'SCHOOL_ADMINISTRATOR'];
        }

        $filterBuilder->andWhere('u.deleted = 0');
        $filterBuilder->addOrderBy('u.lastName', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), /*page number*/ 10 /*limit per page*/);

        $items = $pagination->getItems();

        $notifiedUsers = [];
        /** @var User $item */
        foreach ($items as $item) {

            $shares = $item->getSentToShares();

            foreach ($shares as $share) {

                $alreadyShared = (($experience && $share->getExperience() && $share->getExperience()
                                                                                   ->getId() === $experience->getId() && $share->getSentFrom() && $share->getSentFrom()
                                                                                                                                                        ->getId() === $loggedInUser->getId()) || ($requestEntity && $share->getRequest() && $share->getRequest()
                                                                                                                                                                                                                                                  ->getId() === $requestEntity->getId() && $share->getSentFrom() && $share->getSentFrom()
                                                                                                                                                                                                                                                                                                                          ->getId() === $loggedInUser->getId()));

                if ($alreadyShared) {
                    $notifiedUsers[] = $item->getId();
                    continue 2;
                }
            }
        }

        $items = $this->serializer->serialize($items, 'json', ['groups' => $serializationGroups]);

        $data = [
            'pagination'    => [
                'currentPageNumber' => $pagination->getCurrentPageNumber(),
                'numItemsPerPage'   => $pagination->getItemNumberPerPage(),
                'paginatorOptions'  => $pagination->getPaginatorOptions(),
                'params'            => $pagination->getParams(),
                'totalCount'        => $pagination->getTotalItemCount(),

            ],
            'items'         => json_decode($items, true),
            'schema'        => $this->liform->transform($form),
            'success'       => true,
            'notifiedUsers' => $notifiedUsers,
        ];

        return new JsonResponse($data);
    }
}
