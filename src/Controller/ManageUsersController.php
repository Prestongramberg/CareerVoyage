<?php

namespace App\Controller;

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

/**
 * Class ManageUsersController
 * @package App\Controller
 * @Route("/dashboard/manage-users")
 */
class ManageUsersController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/", name="manage_users", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('manageUsers/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER"})
     * @Route("/professionals", name="manage_professionals", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function professionalsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(ProfessionalUser::class, $this->generateUrl('manage_professionals'));

        $form->handleRequest($request);

        $filterBuilder = $this->professionalUserRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('manageUsers/professionals.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('manage_professionals')
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/site-admins", name="manage_site_admins", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function siteAdminsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(SiteAdminUser::class, $this->generateUrl('manage_site_admins'));
        $form->handleRequest($request);

        $filterBuilder = $this->siteAdminRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if($user->isSiteAdmin()) {
            $filterBuilder->where('u.site = :site')
                ->setParameter('site', $user->getSite());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('manageUsers/site_admins.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('manage_site_admins')
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/state-coordinators", name="manage_state_coordinators", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stateCoordinatorsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(StateCoordinator::class, $this->generateUrl('manage_state_coordinators'));
        $form->handleRequest($request);

        $filterBuilder = $this->stateCoordinatorRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if($user->isSiteAdmin()) {
            $filterBuilder->where('u.site = :site')
                ->setParameter('site', $user->getSite());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('manageUsers/state_coordinators.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('manage_state_coordinators')
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER"})
     * @Route("/regional-coordinators", name="manage_regional_coordinators", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regionalCoordinatorsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(RegionalCoordinator::class, $this->generateUrl('manage_regional_coordinators'));
        $form->handleRequest($request);

        $filterBuilder = $this->regionalCoordinatorRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if($user->isSiteAdmin()) {
            $filterBuilder->where('u.site = :site')
                ->setParameter('site', $user->getSite());
        } elseif ($user->isStateCoordinator()) {
            $filterBuilder->innerJoin('u.region', 'r')
                ->where('u.site = :site')
                ->andWhere('r.state = :state')
                ->setParameter('site', $user->getSite())
                ->setParameter('state', $user->getState());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('manageUsers/regional_coordinators.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('manage_regional_coordinators')
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER"})
     * @Route("/school-administrators", name="manage_school_administrators", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolAdministratorsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(SchoolAdministrator::class, $this->generateUrl('manage_school_administrators'));
        $form->handleRequest($request);

        $filterBuilder = $this->schoolAdministratorRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if($user->isSiteAdmin()) {
            $filterBuilder->where('u.site = :site')
                ->setParameter('site', $user->getSite());
        } elseif ($user->isStateCoordinator()) {
            $filterBuilder->innerJoin('u.schools', 's')
                ->where('u.site = :site')
                ->andWhere('s.state = :state')
                ->setParameter('site', $user->getSite())
                ->setParameter('state', $user->getState());
        } elseif ($user->isRegionalCoordinator()) {
            $filterBuilder->innerJoin('u.schools', 's')
                ->where('u.site = :site')
                ->andWhere('s.region = :region')
                ->setParameter('site', $user->getSite())
                ->setParameter('region', $user->getRegion());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('manageUsers/school_administrators.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('manage_school_administrators')
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/students", name="manage_students", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function studentsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(StudentUser::class, $this->generateUrl('manage_students'));
        $form->handleRequest($request);

        $filterBuilder = $this->studentUserRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if($user->isSiteAdmin()) {
            $filterBuilder->where('u.site = :site')
                ->setParameter('site', $user->getSite());
        } elseif ($user->isStateCoordinator()) {
            $filterBuilder->innerJoin('u.school', 's')
                ->where('u.site = :site')
                ->andWhere('s.state = :state')
                ->setParameter('site', $user->getSite())
                ->setParameter('state', $user->getState());
        } elseif ($user->isRegionalCoordinator()) {
            $filterBuilder->innerJoin('u.school', 's')
                ->where('u.site = :site')
                ->andWhere('s.region = :region')
                ->setParameter('site', $user->getSite())
                ->setParameter('region', $user->getRegion());
        } elseif ($user->isSchoolAdministrator()) {
            $filterBuilder->where('u.site = :site')
                ->andWhere('u.school = :school')
                ->setParameter('site', $user->getSite())
                ->setParameter('school', $user->getSchool());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('manageUsers/students.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('manage_students')
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/educators", name="manage_educators", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function educatorsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(EducatorUser::class, $this->generateUrl('manage_educators'));
        $form->handleRequest($request);

        $filterBuilder = $this->educatorUserRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');

        if($user->isSiteAdmin()) {
            $filterBuilder->where('u.site = :site')
                ->setParameter('site', $user->getSite());
        } elseif ($user->isStateCoordinator()) {
            $filterBuilder->innerJoin('u.school', 's')
                ->where('u.site = :site')
                ->andWhere('s.state = :state')
                ->setParameter('site', $user->getSite())
                ->setParameter('state', $user->getState());
        } elseif ($user->isRegionalCoordinator()) {
            $filterBuilder->innerJoin('u.school', 's')
                ->where('u.site = :site')
                ->andWhere('s.region = :region')
                ->setParameter('site', $user->getSite())
                ->setParameter('region', $user->getRegion());
        } elseif ($user->isSchoolAdministrator()) {
            $filterBuilder->where('u.site = :site')
                ->andWhere('u.school = :school')
                ->setParameter('site', $user->getSite())
                ->setParameter('school', $user->getSchool());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('manageUsers/educators.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('manage_educators')
        ]);
    }

    /**
     * Builds the manage users filter form
     *
     * @param $filterType
     * @param $action
     * @return FormInterface The form
     */
    private function buildFilterForm($filterType, $action)
    {
        $form = $this->createForm(ManageUserFilterType::class, null, [
            'action' => $action,
            'method' => 'GET',
            'filter_type' => $filterType
        ]);

        return $form;
    }
}
