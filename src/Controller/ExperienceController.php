<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\ProfessionalUser;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\ExperienceType;
use App\Form\ManageExperiencesFilterType;
use App\Form\ManageRegistrationsFilterType;
use App\Form\ManageStudentsFilterType;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\RequestService;
use App\Service\UploaderHelper;
use App\Util\AuthorizationVoter;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ExperienceController
 *
 * @package App\Controller
 * @Route("/dashboard/experiences")
 */
class ExperienceController extends AbstractController
{

    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="experience_index", methods={"GET"}, options = {
     *   "expose"
     *   = true })
     * @param  Request  $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        return $this->render('experience/index.html.twig', ['user' => $user,]);
    }

    /**
     * @Route("/personal-calendar", name="experience_personal_calendar",
     *   methods={"GET"}, options = { "expose" = true })
     * @param  Request  $request
     *
     * @return Response
     */
    public function personalCalendarAction(Request $request)
    {
        $user = $this->getUser();

        return $this->render('experience/personal_calendar.html.twig', ['user' => $user,]);
    }

    /**
     * @Route("/list", name="experience_list", methods={"GET"}, options = {
     *   "expose" = true })
     * @param  Request  $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        return $this->redirectToRoute('experience_list_new');

        $user = $this->getUser();

        return $this->render('experience/list.html.twig', ['user' => $user,]);
    }

    /**
     * @Route("/list-new", name="experience_list_new", methods={"GET"}, options
     *   =
     *   { "expose" = true })
     * @param  Request  $request
     *
     * @return Response
     */
    public function listNewAction(Request $request)
    {
        $user = $this->getUser();

        return $this->render('experience/list_new.html.twig', ['user' => $user,]);
    }

    /**
     * There are many different types of experiences. this acts as a central
     * router to route to the specific view for each experience.
     *
     * @Route("/{id}/view", name="experience_view", methods={"GET"}, options = {
     *   "expose" = true })
     * @param  Request     $request
     * @param  Experience  $experience
     *
     * @return Response
     * @throws \ReflectionException
     */
    public function experienceAction(Request $request, Experience $experience)
    {
        $user = $this->getUser();
        switch ($experience->getClassName()) {
            case 'CompanyExperience':
                return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
                break;
            case 'TeachLessonExperience':
                return $this->render('experience/view_teach_lesson_experience.html.twig', ['user' => $user, 'experience' => $experience,]);
                break;
            case 'SchoolExperience':
                return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
                break;
            default:
                return $this->render('experience/generic_experience.html.twig', ['user' => $user, 'experience' => $experience,]);
                break;
        }

        // If for some reason a normal experience is not found then just redirect to the dashboard
        return $this->redirectToRoute('dashboard');
    }


    /**
     * @Route("/{id}/cancel", name="experience_cancel", options = { "expose" =
     *   true })
     * @param  Request     $request
     * @param  Experience  $experience
     *
     * @return RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceCancelAction(
        Request $request,
        Experience $experience
    ) {
        /** @var User $user */
        $user               = $this->getUser();
        $authorizationVoter = new AuthorizationVoter();

        if (!$authorizationVoter->canEditExperience($user, $experience)) {
            throw new AccessDeniedException();
        }

        $message = $request->request->get('cancellationMessage');

        if ($experience->getIsRecurring()) {
            $experience->setCancelled(true);
            $experiences = $experience->getChildEvents();
        } else {
            $experiences = [$experience];
        }

        foreach ($experiences as $experience) {
            $registrations = $experience->getRegistrations();

            foreach ($registrations as $registration) {
                if (!$registration->getUser()
                    ->getEmail()
                ) {
                    continue;
                }

                $this->experienceMailer->experienceCancellationMessage(
                    $experience,
                    $registration->getUser(),
                    $message
                );
            }

            $experience->setCancelled(true);
            $this->entityManager->persist($experience);

            foreach ($registrations as $registration) {
                $this->entityManager->remove($registration);
            }
        }

        $this->entityManager->flush();

        $this->addFlash(
            'success',
            sprintf(
                'Experience successfully cancelled. Users registered for this experience have been notified.'
            )
        );

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * @Route("/{id}/delete", name="experience_delete", options = { "expose" =
     *   true })
     * @param  Request     $request
     * @param  Experience  $experience
     *
     * @return RedirectResponse
     */
    public function experienceDeleteAction(
        Request $request,
        Experience $experience
    ) {
        /** @var User $user */
        $user               = $this->getUser();
        $authorizationVoter = new AuthorizationVoter();

        if (!$authorizationVoter->canEditExperience($user, $experience)) {
            throw new AccessDeniedException();
        }

        $this->entityManager->remove($experience);
        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully deleted.');

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/new", name="experience_new", options = { "expose" =
     *   true })
     * @param  Request  $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        /** @var User $user */
        $user               = $this->getUser();
        $companyId          = $request->query->get('companyId');
        $schoolId           = $request->query->get('schoolId');
        $school             = null;
        $company            = null;
        $authorizationVoter = new AuthorizationVoter();

        if (!$schoolId && !$companyId) {
            throw new AccessDeniedException();
        }

        if ($schoolId) {
            $school = $this->schoolRepository->find($schoolId);

            if (!$school || !$authorizationVoter->canCreateExperiencesForSchool($user, $school)) {
                throw new AccessDeniedException();
            }

            $dataClass        = SchoolExperience::class;
            $validationGroups = ['EXPERIENCE', 'SCHOOL_EXPERIENCE'];
            $experience       = new SchoolExperience();
        }

        if ($companyId) {
            $company = $this->companyRepository->find($companyId);

            if (!$company || !$authorizationVoter->canCreateExperiencesForCompany($user, $company)) {
                throw new AccessDeniedException();
            }

            $dataClass        = CompanyExperience::class;
            $validationGroups = ['EXPERIENCE', 'COMPANY_EXPERIENCE'];
            $experience       = new CompanyExperience();
        }

        if ($request->request->has('changeableField')) {
            $validationGroups = [];
        }

        $form = $this->createForm(ExperienceType::class, $experience, [
            'method'            => 'POST',
            'school'            => $school,
            'company'           => $company,
            'validation_groups' => $validationGroups,
            'data_class'        => $dataClass,
            'action'            => $request->getRequestUri(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SchoolExperience|CompanyExperience $experience */
            $experience = $form->getData();
            $experience->setCreator($user);

            if ($experience instanceof SchoolExperience) {
                $experience->setSchool($school);
            }

            if ($experience instanceof CompanyExperience) {
                $experience->setCompany($company);
            }

            $this->entityManager->persist($experience);
            $this->entityManager->flush();

            if ($experience->getIsRecurring()) {
                $this->addFlash(
                    'success',
                    'Experience successfully created. Please setup your recurring event schedule.'
                );

                $redirectUrl = $this->generateUrl('experience_edit', ['id' => $experience->getId()]).'#experience-schedule';

                return $this->redirect($redirectUrl);
            }

            $this->addFlash('success', 'Experience successfully created!');

            return $this->redirectToRoute('experience_view', ['id' => $experience->getId()]);
        }

        if ($request->request->has('changeableField')) {
            return new JsonResponse([
                'success'    => false,
                'formMarkup' => $this->renderView('experience/new.html.twig', [
                    'school'     => $school,
                    'form'       => $form->createView(),
                    'user'       => $user,
                    'experience' => $experience,
                    'company'    => $company,
                ]),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('experience/new.html.twig', [
            'school'     => $school,
            'form'       => $form->createView(),
            'user'       => $user,
            'experience' => $experience,
            'company'    => $company,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="experience_edit", options = { "expose" = true
     *   })
     * @param  Request     $request
     * @param  Experience  $experience
     *
     * @return Response
     */
    public function editAction(Request $request, Experience $experience)
    {
        /** @var User $user */
        $user               = $this->getUser();
        $authorizationVoter = new AuthorizationVoter();
        $school             = null;
        $company            = null;

        if (!$authorizationVoter->canEditExperience($user, $experience)) {
            throw new AccessDeniedException();
        }

        if ($experience instanceof SchoolExperience) {
            $school           = $experience->getSchool();
            $dataClass        = SchoolExperience::class;
            $validationGroups = ['EXPERIENCE', 'SCHOOL_EXPERIENCE'];
        }

        if ($experience instanceof CompanyExperience) {
            $company          = $experience->getCompany();
            $dataClass        = CompanyExperience::class;
            $validationGroups = ['EXPERIENCE', 'COMPANY_EXPERIENCE'];
        }

        if ($request->request->has('changeableField')) {
            $validationGroups = [];
        }

        if (!$experience->getAddressSearch()) {
            $experience->setAddressSearch($experience->getFormattedAddress());
        }

        $form = $this->createForm(ExperienceType::class, $experience, [
            'method'            => 'POST',
            'school'            => $school,
            'company'           => $company,
            'validation_groups' => $validationGroups,
            'data_class'        => $dataClass,
            'action'            => $request->getRequestUri(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SchoolExperience|CompanyExperience $experience */
            $experience = $form->getData();

            $this->entityManager->persist($experience);
            $this->entityManager->flush();

            $this->addFlash('success', 'Experience successfully updated!');

            return $this->redirectToRoute('experience_edit', ['id' => $experience->getId()]);
        }

        if ($request->request->has('changeableField')) {
            return new JsonResponse([
                'success'    => false,
                'formMarkup' => $this->renderView('experience/edit.html.twig', [
                    'school'     => $school,
                    'form'       => $form->createView(),
                    'user'       => $user,
                    'experience' => $experience,
                    'company'    => $company,
                ]),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('experience/edit.html.twig', [
            'school'     => $school,
            'form'       => $form->createView(),
            'user'       => $user,
            'experience' => $experience,
            'company'    => $company,
        ]);
    }

    /**
     * @Route("/{id}/view", name="experience_view", options = { "expose" = true
     *   })
     * @param  Request     $request
     * @param  Experience  $experience
     *
     * @return Response
     */
    public function viewAction(Request $request, Experience $experience)
    {
        $user = $this->getUser();

        return $this->render('experience/view.html.twig', [
            'user'               => $user,
            'experience'         => $experience,
            //'students' => $experience->getSchool()->getStudentUsers(),
            'authorizationVoter' => new AuthorizationVoter(),

        ]);
    }

    /**
     * @Route("/{id}/register", name="experience_register", options = { "expose"
     *   = true }, methods={"GET"})
     * @param  Request         $request
     * @param  Experience      $experience
     *
     * @param  RequestService  $requestService
     *
     * @return RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function registerAction(
        Request $request,
        Experience $experience,
        RequestService $requestService
    ) {
        // We need to check if the experience requires approval from the event creator. If so follow the
        // current flow, otherwise, bypass sending emails and mark the registration as complete.
        $req    = $request;
        $userId = $request->query->get('userId', null);
        if ($userId) {
            $createdBy      = $this->getUser();
            $userToRegister = $this->userRepository->find($userId);
        } else {
            $createdBy      = $this->getUser();
            $userToRegister = $this->getUser();
        }

        $registrationRequest = $requestService->createRegistrationRequest(
            $createdBy,
            $userToRegister,
            $experience
        );

        return $this->redirectToRoute('experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @Route("/{id}/unregister", name="experience_unregister", options = {
     *   "expose" = true }, methods={"GET"})
     * @param  Request         $request
     * @param  Experience      $experience
     *
     * @param  RequestService  $requestService
     *
     * @throws NonUniqueResultException
     */
    public function unregisterAction(
        Request $request,
        Experience $experience,
        RequestService $requestService
    ) {
        // We need to check if the experience requires approval from the event creator. If so follow the
        // current flow, otherwise, bypass sending emails and mark the registration as complete.
        $req    = $request;
        $userId = $request->query->get('userId', null);
        if ($userId) {
            $user = $this->userRepository->find($userId);
        } else {
            $user = $this->getUser();
        }

        $registration = $this->registrationRepository->getByUserAndExperience(
            $user,
            $experience
        );

        if ($registration) {
            $this->entityManager->remove($registration);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @Route("/manage", name="experiences_manage", methods={"GET"})
     * @param  Request  $request
     *
     * @return Response
     */
    public function manageExperiencesAction(Request $request)
    {
        /** @var User $user */
        $user               = $this->getUser();
        $schoolId           = $request->query->get('schoolId');
        $companyId          = $request->query->get('companyId');
        $parentExperienceId = $request->query->get('parentExperienceId');
        $school             = null;
        $company            = null;
        $parentExperience   = null;
        $schools            = [];
        $actionUrlParams    = [];
        $authorizationVoter = new AuthorizationVoter();

        if (!$schoolId && !$companyId) {
            if ($user instanceof SchoolAdministrator) {
                $school = $user->getSchools()
                    ->first();

                if ($school) {
                    return $this->redirectToRoute('experiences_manage', ['schoolId' => $school->getId()]);
                }

                throw new AccessDeniedException();
            } elseif ($user instanceof EducatorUser) {
                $school = $user->getSchool();

                if ($school) {
                    return $this->redirectToRoute('experiences_manage', ['schoolId' => $school->getId()]);
                }

                throw new AccessDeniedException();
            } elseif ($user instanceof ProfessionalUser) {
                $company = $user->getOwnedCompany();

                if ($company) {
                    return $this->redirectToRoute('experiences_manage', ['companyId' => $company->getId()]);
                }
            }

            throw new AccessDeniedException();
        }


        if ($schoolId) {
            $school = $this->schoolRepository->find($schoolId);

            if (!$school || !$authorizationVoter->canCreateExperiencesForSchool($user, $school)) {
                throw new AccessDeniedException();
            }

            $actionUrlParams['schoolId'] = $schoolId;
        }

        if ($parentExperienceId) {
            $parentExperience = $this->experienceRepository->find($parentExperienceId);

            if (!$parentExperience) {
                throw new AccessDeniedException();
            }

            $actionUrlParams['parentExperienceId'] = $parentExperienceId;
        }

        if ($companyId) {
            $company = $this->companyRepository->find($companyId);

            // todo change permissions for company
            if (!$company || !$authorizationVoter->canCreateExperiencesForCompany($user, $company)) {
                throw new AccessDeniedException();
            }

            $actionUrlParams['companyId'] = $companyId;
        }


        $actionUrl = $this->generateUrl('experiences_manage', $actionUrlParams);

        $form = $this->createForm(ManageExperiencesFilterType::class, null, [
            'action' => $actionUrl,
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        $schoolIds = [$schoolId];


        if ($user instanceof SchoolAdministrator) {
            $schools = $user->getSchools();

            $filterBuilder = $this->schoolExperienceRepository->createQueryBuilder('e')
                ->innerJoin('e.school', 'school')
                ->andWhere('school.id IN (:schoolIds)')
                ->setParameter('schoolIds', $schoolIds);
        } elseif ($user instanceof EducatorUser) {
            $schools = new ArrayCollection([$user->getSchool()]);

            $filterBuilder = $this->schoolExperienceRepository->createQueryBuilder('e')
                ->innerJoin('e.school', 'school')
                ->leftJoin('e.schoolContact', 'schoolContact')
                ->leftJoin('e.creator', 'creator')
                ->andWhere(
                    'schoolContact.id = :schoolContactId or creator.id = :creatorId'
                )
                ->setParameter('schoolContactId', $user->getId())
                ->setParameter('creatorId', $user->getId());
        } elseif ($user instanceof ProfessionalUser) {
            // todo query company experiences that you manage or created or belong to your company if you are the owner?
            //$schools = $this->schoolRepository->findAll();

            $filterBuilder = $this->companyExperienceRepository->createQueryBuilder('e')
                ->innerJoin('e.company', 'company')
                ->leftJoin('e.employeeContact', 'employeeContact')
                ->leftJoin('e.creator', 'creator')
                ->leftJoin('company.owner', 'owner')
                ->andWhere(
                    'employeeContact.id = :employeeContactId or creator.id = :creatorId or owner.id = :ownerId'
                )
                ->setParameter('employeeContactId', $user->getId())
                ->setParameter('creatorId', $user->getId())
                ->setParameter('ownerId', $user->getId());
        } elseif ($user instanceof AdminUser) {
            $schools = $this->schoolRepository->findAll();
        }

        if ($parentExperienceId) {
            //$filterBuilder->innerJoin('e.parentEvent', 'parentEvent');
            $filterBuilder->andWhere('e.parentEvent = :parentEventId')
                ->setParameter('parentEventId', $parentExperienceId);
        } else {
            $filterBuilder->andWhere('e.parentEvent IS NULL');
        }

        // We don't show child events from recurring events on this page
        $filterBuilder->addOrderBy('e.startDateAndTime', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        if ($request->query->get('limit') === 'all') {
            $pagination = $this->paginator->paginate(
                $filterQuery,
                /* query NOT result */ $request->query->getInt('page', 1),
                100000000
            );
        } else {
            $pagination = $this->paginator->paginate(
                $filterQuery,
                /* query NOT result */ $request->query->getInt('page', 1),
                /*page number*/ $request->query->getInt('limit', 10)
            );
        }

        $clearFormUrl = $this->generateUrl('experiences_manage', $actionUrlParams);

        return $this->render('experience/manage_experiences.html.twig', [
            'user'             => $user,
            'pagination'       => $pagination,
            'school'           => $school,
            'company'          => $company,
            'clearFormUrl'     => $clearFormUrl,
            'form'             => $form->createView(),
            'schools'          => $schools,
            'parentExperience' => $parentExperience,
        ]);
    }

    /**
     * @Route("/{id}/registrations", name="experience_registrations",
     *   methods={"GET"})
     * @param  Experience  $experience
     * @param  Request     $request
     *
     * @return Response
     */
    public function registrationsAction(
        Experience $experience,
        Request $request
    ) {
        /** @var User $user */
        $user        = $this->getUser();
        $schoolId    = $request->query->get('schoolId');
        $companyId   = $request->query->get('companyId');
        $school      = null;
        $company     = null;
        $schools     = [];
        $experiences = [];

        if ($experience instanceof SchoolExperience && $experience->getSchool()) {
            $schoolId = $experience->getSchool()
                ->getId();

            if ($user instanceof SchoolAdministrator) {
                // todo if a school administrator created an event, they should always be able to manage it right?

                $schools     = $user->getSchools();
                $experiences = $this->schoolExperienceRepository->createQueryBuilder('e')
                    ->innerJoin('e.school', 'school')
                    ->andWhere(
                        'school.id = :schoolId'
                    )
                    ->setParameter('schoolId', $schoolId)
                    ->getQuery()
                    ->getResult();
            } elseif ($user instanceof EducatorUser) {
                $schools = new ArrayCollection([$user->getSchool()]);

                $experiences = $this->schoolExperienceRepository->createQueryBuilder('e')
                    ->innerJoin('e.school', 'school')
                    ->leftJoin(
                        'e.schoolContact',
                        'schoolContact'
                    )
                    ->leftJoin('e.creator', 'creator')
                    ->andWhere(
                        'schoolContact.id = :schoolContactId or creator.id = :creatorId'
                    )
                    ->setParameter('schoolContactId', $user->getId())
                    ->setParameter('creatorId', $user->getId())
                    ->getQuery()
                    ->getResult();
            }
        }

        if ($experience instanceof CompanyExperience && $experience->getCompany()) {
            $companyIdId = $experience->getCompany()
                ->getId();

            if ($user instanceof ProfessionalUser) {
                $experiences = $this->companyExperienceRepository->createQueryBuilder('e')
                    ->innerJoin('e.company', 'company')
                    ->leftJoin('e.employeeContact', 'employeeContact')
                    ->leftJoin('e.creator', 'creator')
                    ->leftJoin('company.owner', 'owner')
                    ->andWhere(
                        'employeeContact.id = :employeeContactId or creator.id = :creatorId or owner.id = :ownerId'
                    )
                    ->setParameter('employeeContactId', $user->getId())
                    ->setParameter('creatorId', $user->getId())
                    ->setParameter('ownerId', $user->getId())
                    ->getQuery()
                    ->getResult();
            }
        }

        $authorizationVoter = new AuthorizationVoter();

        if (!$authorizationVoter->canEditExperience($user, $experience)) {
            throw new AccessDeniedException();
        }

        $schoolIds = [$schoolId];

        $form = $this->createForm(ManageRegistrationsFilterType::class, null, [
            'action' => $this->generateUrl('experience_registrations', ['id' => $experience->getId()]),
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        $filterBuilder = $this->registrationRepository->createQueryBuilder('r')
            ->innerJoin(
                'r.experience',
                'experience'
            )
            ->innerJoin('r.user', 'user')
            ->andWhere(
                'experience.id = :experienceId'
            )
            ->setParameter('experienceId', $experience->getId());

        $filterBuilder->addOrderBy('user.lastName', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        if ($request->query->get('limit') === 'all') {
            $pagination = $this->paginator->paginate(
                $filterQuery,
                /* query NOT result */ $request->query->getInt('page', 1),
                100000000
            );
        } else {
            $pagination = $this->paginator->paginate(
                $filterQuery,
                /* query NOT result */ $request->query->getInt('page', 1),
                /*page number*/ $request->query->getInt('limit', 10)
            );
        }

        if ($school) {
            $clearFormUrl = $this->generateUrl('experience_registrations', ['id' => $experience->getId(), 'schoolId' => $school->getId()]);
        } else {
            $clearFormUrl = $this->generateUrl('experience_registrations', ['id' => $experience->getId()]);
        }

        return $this->render('experience/registrations.html.twig', [
            'user'         => $user,
            'pagination'   => $pagination,
            'school'       => $school,
            'clearFormUrl' => $clearFormUrl,
            'form'         => $form->createView(),
            'schools'      => $schools,
            'experience'   => $experience,
            'experiences'  => $experiences,
        ]);
    }

}