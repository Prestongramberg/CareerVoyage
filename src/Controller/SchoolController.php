<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolAdministratorRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EditSchoolType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\NewSchoolType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
 * Class SchoolController
 * @package App\Controller
 * @Route("/dashboard")
 */
class SchoolController extends AbstractController
{
    use FileHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var Packages
     */
    private $assetsManager;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var CompanyPhotoRepository
     */
    private $companyPhotoRepository;

    /**
     * @var LessonFavoriteRepository
     */
    private $lessonFavoriteRepository;

    /**
     * @var LessonTeachableRepository
     */
    private $lessonTeachableRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var RequestsMailer
     */
    private $requestsMailer;

    /**
     * @var SecurityMailer
     */
    private $securityMailer;

    /**
     * SchoolController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param LessonFavoriteRepository $lessonFavoriteRepository
     * @param LessonTeachableRepository $lessonTeachableRepository
     * @param UserRepository $userRepository
     * @param RequestsMailer $requestsMailer
     * @param SecurityMailer $securityMailer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        Packages $assetsManager,
        CompanyRepository $companyRepository,
        CompanyPhotoRepository $companyPhotoRepository,
        LessonFavoriteRepository $lessonFavoriteRepository,
        LessonTeachableRepository $lessonTeachableRepository,
        UserRepository $userRepository,
        RequestsMailer $requestsMailer,
        SecurityMailer $securityMailer
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
        $this->lessonFavoriteRepository = $lessonFavoriteRepository;
        $this->lessonTeachableRepository = $lessonTeachableRepository;
        $this->userRepository = $userRepository;
        $this->requestsMailer = $requestsMailer;
        $this->securityMailer = $securityMailer;
    }

    /**
     * @Security("is_granted('ROLE_REGIONAL_COORDINATOR_USER')")
     * @Route("/schools/new", name="school_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function newAction(Request $request) {

        $user = $this->getUser();
        $school = new School();

        $form = $this->createForm(NewSchoolType::class, $school, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            $this->entityManager->persist($school);

            $email = $form->get('schoolAdministratorEmail')->getData();
            $firstName = $form->get('schoolAdministratorFirstName')->getData();
            $lastName = $form->get('schoolAdministratorLastName')->getData();

            $existingUser = $this->userRepository->getByEmailAddress($email);

            if($existingUser) {
                $this->addFlash('error', 'That user already exists in the system.');
                return $this->redirectToRoute('school_new');
            } else {
                $schoolAdministrator = new SchoolAdministrator();
                $schoolAdministrator->setEmail($email);
                $schoolAdministrator->setFirstName($firstName);
                $schoolAdministrator->setLastName($lastName);
                $schoolAdministrator->setEmail($email);
                $schoolAdministrator->initializeNewUser();
                $schoolAdministrator->setPasswordResetToken();
                $this->entityManager->persist($schoolAdministrator);
            }

            $schoolAdministratorRequest = new SchoolAdministratorRequest();
            $schoolAdministratorRequest->setSchool($school);
            $schoolAdministratorRequest->setCreatedBy($this->getUser());
            $schoolAdministratorRequest->setNeedsApprovalBy($schoolAdministrator);
            $this->entityManager->persist($schoolAdministratorRequest);

            $this->entityManager->flush();
            $this->securityMailer->sendAccountActivation($schoolAdministrator);
            $this->requestsMailer->schoolAdministratorRequest($schoolAdministratorRequest);

            $this->addFlash('success', sprintf('School successfully created. Invite sent to %s', $email));
            return $this->redirectToRoute('school_new');
        }

        return $this->render('school/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Security("is_granted('ROLE_SCHOOL_ADMINISTRATOR_USER')")
     * @Route("/schools/{id}/edit", name="school_edit")
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, School $school) {

        $user = $this->getUser();

        $this->denyAccessUnlessGranted('edit', $school);

        $form = $this->createForm(EditSchoolType::class, $school, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            $this->entityManager->persist($school);
            $this->entityManager->flush();


            $this->addFlash('success', sprintf('School successfully updated.'));
            return $this->redirectToRoute('school_edit');
        }

        return $this->render('school/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'school' => $school
        ]);
    }
}