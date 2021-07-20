<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class UserController
 *
 * @package App\Controller
 * @Route("/api")
 */
class UserController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/logged-in-user", name="logged_in_user", methods={"GET"}, options = { "expose" = true })
     */
    public function getLoggedInUser()
    {

        $json = $this->serializer->serialize($this->getUser(), 'json', ['groups' => ['ALL_USER_DATA',
                                                                                     'LESSON_DATA',
                                                                                     'PROFESSIONAL_USER_DATA',
        ],
        ]);

        $payload = json_decode($json, true);

        $payload['requests']                   = json_decode($this->serializer->serialize($this->getUser()->getRequests(), 'json', ['groups' => ['REQUEST']]), true);
        $payload['requestsThatNeedMyApproval'] = json_decode($this->serializer->serialize($this->getUser()->getRequestsThatNeedMyApproval(), 'json', ['groups' => ['REQUEST']]), true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/users/select2-search", name="users_select2_search", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function usersSelect2Search(Request $request)
    {
        $limit    = 10;
        $userRole = $request->query->get('userRole', null);
        $schools  = $request->query->get('schools', []);
        $regions  = $request->query->get('regions', []);
        $search   = $request->query->get('search', null);

        switch ($userRole) {
            case User::ROLE_PROFESSIONAL_USER:

                $queryBuilder = $this->professionalUserRepository->createQueryBuilder('u')
                                                                 ->leftJoin('u.regions', 'regions')
                                                                 ->leftJoin('u.schools', 'schools')
                                                                 ->addOrderBy('u.lastName', 'ASC');

                $regionQueryParts = [];
                foreach ($regions as $region) {
                    $regionQueryParts[] = sprintf('regions.id = %s', $region);
                }

                if (!empty($regionQueryParts)) {
                    $regionQueryString = implode(" OR ", $regionQueryParts);
                    $queryBuilder->andWhere($regionQueryString);
                }

                $schoolQueryParts = [];
                foreach ($schools as $school) {
                    $schoolQueryParts[] = sprintf('schools.id = %s', $school);
                }

                if (!empty($schoolQueryParts)) {
                    $schoolQueryString = implode(" OR ", $schoolQueryParts);
                    $queryBuilder->andWhere($schoolQueryString);
                }

                break;

            case User::ROLE_EDUCATOR_USER:

                $queryBuilder = $this->educatorUserRepository->createQueryBuilder('u')
                                                             ->leftJoin('u.school', 'school')
                                                             ->leftJoin('school.region', 'region')
                                                             ->addOrderBy('u.lastName', 'ASC');

                $regionQueryParts = [];
                foreach ($regions as $region) {
                    $regionQueryParts[] = sprintf('region.id = %s', $region);
                }

                if (!empty($regionQueryParts)) {
                    $regionQueryString = implode(" OR ", $regionQueryParts);
                    $queryBuilder->andWhere($regionQueryString);
                }

                $schoolQueryParts = [];
                foreach ($schools as $school) {
                    $schoolQueryParts[] = sprintf('school.id = %s', $school);
                }

                if (!empty($schoolQueryParts)) {
                    $schoolQueryString = implode(" OR ", $schoolQueryParts);
                    $queryBuilder->andWhere($schoolQueryString);
                }

                break;

            case User::ROLE_SCHOOL_ADMINISTRATOR_USER:

                $queryBuilder = $this->schoolAdministratorRepository->createQueryBuilder('u')
                                                                    ->leftJoin('u.schools', 'schools')
                                                                    ->leftJoin('schools.region', 'region')
                                                                    ->addOrderBy('u.lastName', 'ASC');

                $regionQueryParts = [];
                foreach ($regions as $region) {
                    $regionQueryParts[] = sprintf('region.id = %s', $region);
                }

                if (!empty($regionQueryParts)) {
                    $regionQueryString = implode(" OR ", $regionQueryParts);
                    $queryBuilder->andWhere($regionQueryString);
                }

                $schoolQueryParts = [];
                foreach ($schools as $school) {
                    $schoolQueryParts[] = sprintf('schools.id = %s', $school);
                }

                if (!empty($schoolQueryParts)) {
                    $schoolQueryString = implode(" OR ", $schoolQueryParts);
                    $queryBuilder->andWhere($schoolQueryString);
                }

                break;

            case User::ROLE_REGIONAL_COORDINATOR_USER:

                $queryBuilder = $this->regionalCoordinatorRepository->createQueryBuilder('u')
                                                                    ->leftJoin('u.region', 'region')
                                                                    ->leftJoin('region.schools', 'schools')
                                                                    ->addOrderBy('u.lastName', 'ASC');

                $regionQueryParts = [];
                foreach ($regions as $region) {
                    $regionQueryParts[] = sprintf('region.id = %s', $region);
                }

                if (!empty($regionQueryParts)) {
                    $regionQueryString = implode(" OR ", $regionQueryParts);
                    $queryBuilder->andWhere($regionQueryString);
                }

                $schoolQueryParts = [];
                foreach ($schools as $school) {
                    $schoolQueryParts[] = sprintf('schools.id = %s', $school);
                }

                if (!empty($schoolQueryParts)) {
                    $schoolQueryString = implode(" OR ", $schoolQueryParts);
                    $queryBuilder->andWhere($schoolQueryString);
                }

                break;

            case User::ROLE_COMPANY_ADMINISTRATOR:

                $queryBuilder = $this->professionalUserRepository->createQueryBuilder('u')
                                                                 ->leftJoin('u.regions', 'regions')
                                                                 ->leftJoin('u.schools', 'schools')
                                                                 ->andWhere('u.roles LIKE :role')
                                                                 ->setParameter('role', '%"' . User::ROLE_COMPANY_ADMINISTRATOR . '"%')
                                                                 ->addOrderBy('u.lastName', 'ASC');


                $regionQueryParts = [];
                foreach ($regions as $region) {
                    $regionQueryParts[] = sprintf('regions.id = %s', $region);
                }

                if (!empty($regionQueryParts)) {
                    $regionQueryString = implode(" OR ", $regionQueryParts);
                    $queryBuilder->andWhere($regionQueryString);
                }

                $schoolQueryParts = [];
                foreach ($schools as $school) {
                    $schoolQueryParts[] = sprintf('schools.id = %s', $school);
                }

                if (!empty($schoolQueryParts)) {
                    $schoolQueryString = implode(" OR ", $schoolQueryParts);
                    $queryBuilder->andWhere($schoolQueryString);
                }

                break;

            default:

                $queryBuilder = $this->userRepository->createQueryBuilder('u')
                                                     ->addOrderBy('u.lastName', 'ASC');
                break;
        }

        // empty names results in empty options in the dropdown so let's avoid that
        $queryBuilder->andWhere("u.firstName IS NOT NULL and u.lastName IS NOT NULL and u.firstName != '' and u.lastName != ''");

        if($search) {
            $queryBuilder->andWhere('CONCAT(u.firstName, \' \', u.lastName) LIKE :searchTerm')
                         ->setParameter('searchTerm', '%' . $search . '%');
        }


        $query = $queryBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1),
            $limit
        );

        $data = [];

        /** @var User $item */
        foreach ($pagination->getItems() as $item) {
            $data[] = ['id' => $item->getId(), 'text' => $item->getFullName()];
        }


        $currentPageNumber = $pagination->getCurrentPageNumber();
        $totalItemCount    = $pagination->getTotalItemCount();

        $hasMore = true;
        if (($limit * $currentPageNumber) >= $totalItemCount) {
            $hasMore = false;
        }

        return new JsonResponse(
            [
                'results' => $data,
                'pagination' => [
                    'more' => $hasMore,
                ],
            ],
            Response::HTTP_OK
        );
    }
}