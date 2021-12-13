<?php

namespace App\Controller;

use App\Entity\CompanyExperience;
use App\Entity\Experience;
use App\Entity\School;
use App\Entity\SchoolExperience;
use App\Entity\User;
use App\Form\ExperienceType;
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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/", name="experience_index", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {

        $user = $this->getUser();

        return $this->render('experience/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/list", name="experience_list", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {


        $user = $this->getUser();

        return $this->render('experience/list.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * There are many different types of experiences. this acts as a central router
     * to route to the specific view for each experience.
     *
     * @Route("/{id}/view", name="experience_view", methods={"GET"}, options = { "expose" = true })
     * @param Request    $request
     * @param Experience $experience
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
                return $this->render('experience/view_teach_lesson_experience.html.twig', [
                    'user'       => $user,
                    'experience' => $experience,
                ]);
                break;
            case 'SchoolExperience':
                return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
                break;
            default:
                return $this->render('experience/generic_experience.html.twig', [
                    'user'       => $user,
                    'experience' => $experience,
                ]);
                break;
        }

        // If for some reason a normal experience is not found then just redirect to the dashboard
        return $this->redirectToRoute('dashboard');
    }


    /**
     * @Route("/{id}/cancel", name="experience_cancel", options = { "expose" = true })
     * @param Request          $request
     * @param Experience $experience
     *
     * @return RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceCancelAction(Request $request, Experience $experience)
    {
        /** @var User $user */
        $user               = $this->getUser();
        $authorizationVoter = new AuthorizationVoter();

        if (!$authorizationVoter->canEditExperience($user, $experience)) {
            throw new AccessDeniedException();
        }

        $message = $request->request->get('cancellationMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {

            if (!$registration->getUser()->getEmail()) {
                continue;
            }

            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $message);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach ($registrations as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Experience successfully cancelled. Users registered for this experience have been notified.'));

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * @Route("/{id}/delete", name="experience_delete", options = { "expose" = true })
     * @param Request          $request
     * @param Experience $experience
     *
     * @return RedirectResponse
     */
    public function experienceDeleteAction(Request $request, Experience $experience)
    {

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
     * @Route("/experiences/new", name="experience_new", options = { "expose" = true })
     * @param Request $request
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

        if ($schoolId && $school = $this->schoolRepository->find($schoolId)) {
            if (!$authorizationVoter->canCreateExperiencesForSchool($user, $school)) {
                throw new AccessDeniedException();
            }

            $dataClass        = SchoolExperience::class;
            $validationGroups = ['EXPERIENCE', 'SCHOOL_EXPERIENCE'];
            $experience       = new SchoolExperience();
        }

        if ($companyId && $company = $this->companyRepository->find($companyId)) {
            if (!$authorizationVoter->canEditCompany($user, $company)) {
                throw new AccessDeniedException();
            }

            $dataClass        = CompanyExperience::class;
            $validationGroups = ['EXPERIENCE', 'COMPANY_EXPERIENCE'];
            $experience       = new CompanyExperience();
        }

        $form = $this->createForm(ExperienceType::class, $experience, [
            'method'            => 'POST',
            'school'            => $school,
            'company'           => $company,
            'validation_groups' => $validationGroups,
            'data_class'        => $dataClass,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SchoolExperience|CompanyExperience $experience */
            $experience = $form->getData();

            if ($experience instanceof SchoolExperience) {
                $experience->setSchool($school);
            }

            if ($experience instanceof CompanyExperience) {
                $experience->setCompany($company);
            }

            $this->entityManager->persist($experience);
            $this->entityManager->flush();

            $this->addFlash('success', 'Experience successfully created!');

            return $this->redirectToRoute('experience_view', ['id' => $experience->getId()]);
        }

        return $this->render('experience/new.html.twig', [
            'school'     => $school,
            'form'       => $form->createView(),
            'user'       => $user,
            'experience' => $experience,
            'company' => $company
        ]);
    }

    /**
     * @Route("/{id}/edit", name="experience_edit", options = { "expose" = true })
     * @param Request    $request
     * @param Experience $experience
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

        $form = $this->createForm(ExperienceType::class, $experience, [
            'method'            => 'POST',
            'school'            => $school,
            'company'           => $company,
            'validation_groups' => $validationGroups,
            'data_class'        => $dataClass,
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

        return $this->render('experience/edit.html.twig', [
            'school'     => $school,
            'form'       => $form->createView(),
            'user'       => $user,
            'experience' => $experience,
        ]);
    }

    /**
     * @Route("/{id}/view", name="experience_view", options = { "expose" = true })
     * @param Request    $request
     * @param Experience $experience
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
     * @Route("/{id}/register", name="experience_register", options = { "expose" = true }, methods={"GET"})
     * @param Request          $request
     * @param Experience $experience
     *
     * @param RequestService   $requestService
     *
     * @return RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function registerAction(
        Request $request, Experience $experience, RequestService $requestService)
    {
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

        $registrationRequest = $requestService->createRegistrationRequest($createdBy, $userToRegister, $experience);

        return $this->redirectToRoute('experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @Route("/{id}/unregister", name="experience_unregister", options = { "expose" = true }, methods={"GET"})
     * @param Request          $request
     * @param Experience $experience
     *
     * @param RequestService   $requestService
     *
     * @throws NonUniqueResultException
     */
    public function unregisterAction(
        Request $request, Experience $experience, RequestService $requestService)
    {
        // We need to check if the experience requires approval from the event creator. If so follow the
        // current flow, otherwise, bypass sending emails and mark the registration as complete.
        $req    = $request;
        $userId = $request->query->get('userId', null);
        if ($userId) {
            $user = $this->userRepository->find($userId);
        } else {
            $user = $this->getUser();
        }

        $registration = $this->registrationRepository->getByUserAndExperience($user, $experience);

        if ($registration) {
            $this->entityManager->remove($registration);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('experience_view', ['id' => $experience->getId()]);
    }

}