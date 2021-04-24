<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\ManageUserFilterType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\SearchFilterType;
use App\Repository\AdminUserRepository;
use App\Repository\CompanyFavoriteRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        return $this->render(
            'manageUsers/index.html.twig', [
                                             'user' => $user,
                                         ]
        );
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
        $user         = $loggedInUser = $this->getUser();
        $regionIds    = [];
        $experienceId = $request->query->get('experience', null);
        $experience   = null;
        if ($experienceId) {
            $experience = $this->experienceRepository->find($experienceId);
        }

        /**
         * Students can message
         * 1. Educators that are part of the same school
         * 2. School administrators that are part of the same school
         * 3. Students that are part of the same school
         *
         * @var StudentUser $loggedInUser
         */
        if ($loggedInUser->isStudent()) {

            /** @var StudentUser $loggedInUser */
            $regionIds = [];

            if ($loggedInUser->getSchool() && $loggedInUser->getSchool()->getRegion()) {
                $regionIds[] = $loggedInUser->getSchool()->getRegion()->getId();
            }
        }

        /**
         * Educators can message
         * 1. Educators that are part of the same school
         * 2. School administrators that are part of the same school
         * 3. Students that are part of the same school
         * 4. All Professional Users
         *
         * @var EducatorUser $loggedInUser
         */
        if ($loggedInUser->isEducator()) {

            /** @var EducatorUser $loggedInUser */
            $regionIds = [];

            if ($loggedInUser->getSchool() && $loggedInUser->getSchool()->getRegion()) {
                $regionIds[] = $loggedInUser->getSchool()->getRegion()->getId();
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

            /** @var ProfessionalUser $loggedInUser */
            $regionIds = [];
            foreach ($loggedInUser->getRegions() as $region) {
                $regionIds[] = $region->getId();
            }
        }

        /**
         * School Administrators can message
         * 1. All educators on the platform
         * 2. All school administrators
         * 4. All Professional Users
         *
         * @var SchoolAdministrator $loggedInUser
         */
        if ($loggedInUser->isSchoolAdministrator()) {

            $regionIds = [];
            /** @var SchoolAdministrator $loggedInUser */
            foreach ($loggedInUser->getSchools() as $school) {

                if (!$school->getRegion()) {
                    continue;
                }

                $regionIds[] = $school->getRegion()->getId();
            }
        }


        // TODO CAN I USE THE PROFESSIONAL FILTER TYPE AND SOME OF THESE OTHER FILTER OPTIONS THAT
        //  WERE ALREADY CREATED. AND HOW ABOUT WE ALLOW FOR JUST ONE USER ROLE BEING ABLE TO BE SELECTED AT A TIME


        $filters  = $request->request->get('filters', []);
        $userRole = $filters['userRole'] ?? null;

        $form = $this->createForm(
            SearchFilterType::class, null, [
                                       'method'   => 'GET',
                                       'userRole' => $userRole,
                                   ]
        );

        $form->submit($request->request->get('filters'));

        $userRole = $form->get('userRole')->getData();

        if ($userRole === User::ROLE_PROFESSIONAL_USER) {
            $filterBuilder = $this->professionalUserRepository->createQueryBuilder('u');

            if (!empty($regionIds)) {
                $filterBuilder->innerJoin('u.regions', 'regions')
                              ->andWhere('regions.id IN (:regionIds)')
                              ->setParameter('regionIds', $regionIds);
            }

        } elseif ($userRole === User::ROLE_EDUCATOR_USER) {
            $filterBuilder = $this->educatorUserRepository->createQueryBuilder('u');

            if (!empty($regionIds)) {
                $filterBuilder->innerJoin('u.school', 'school')
                              ->innerJoin('school.region', 'region')
                              ->andWhere('region.id IN (:regionIds)')
                              ->setParameter('regionIds', $regionIds);
            }

        } elseif ($userRole === User::ROLE_STUDENT_USER) {
            $filterBuilder = $this->studentUserRepository->createQueryBuilder('u');

            if (!empty($regionIds)) {
                $filterBuilder->innerJoin('u.school', 'school')
                              ->innerJoin('school.region', 'region')
                              ->andWhere('region.id IN (:regionIds)')
                              ->setParameter('regionIds', $regionIds);
            }

        } elseif ($userRole === User::ROLE_SCHOOL_ADMINISTRATOR_USER) {
            $filterBuilder = $this->schoolAdministratorRepository->createQueryBuilder('u');

            if (!empty($regionIds)) {
                $filterBuilder->innerJoin('u.schools', 'schools')
                              ->innerJoin('schools.region', 'region')
                              ->andWhere('region.id IN (:regionIds)')
                              ->setParameter('regionIds', $regionIds);
            }

        } else {
            $filterBuilder = $this->userRepository->createQueryBuilder('u');
        }

        $filterBuilder->andWhere('u.deleted = 0');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $items = $pagination->getItems();

        $notifiedUsers = [];
        /** @var User $item */
        foreach ($items as $item) {

            $shares = $item->getSentToShares();

            foreach ($shares as $share) {

                if ($share->getExperience() && $experience && $share->getExperience()->getId() !== $experience->getId()) {
                    continue;
                }

                if ($share->getSentFrom() && $share->getSentFrom()->getId() === $loggedInUser->getId()) {
                    $notifiedUsers[] = $item->getId();
                    continue 2;
                }
            }
        }

        $items = $this->serializer->serialize($items, 'json', ['groups' => ["ALL_USER_DATA"]]);

        return new JsonResponse(
            [
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
            ]
        );
    }
}
