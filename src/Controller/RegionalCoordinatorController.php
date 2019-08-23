<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RegionalCoordinatorFormType;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StateCoordinatorRequestRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
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
 * Class RegionalCoordinatorController
 * @package App\Controller
 * @Route("/dashboard/regional-coordinator")
 */
class RegionalCoordinatorController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;

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
     * @var SecurityMailer
     */
    private $securityMailer;

    /**
     * @var RequestsMailer
     */
    private $requestsMailer;

    /**
     * @var StateCoordinatorRequestRepository
     */
    private $stateCoordinatorRequestRepository;

    /**
     * @var StateCoordinatorRepository
     */
    private $stateCoordinatorRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * StateCoordinatorController constructor.
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
     * @param SecurityMailer $securityMailer
     * @param RequestsMailer $requestsMailer
     * @param StateCoordinatorRequestRepository $stateCoordinatorRequestRepository
     * @param StateCoordinatorRepository $stateCoordinatorRepository
     * @param UserRepository $userRepository
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
        SecurityMailer $securityMailer,
        RequestsMailer $requestsMailer,
        StateCoordinatorRequestRepository $stateCoordinatorRequestRepository,
        StateCoordinatorRepository $stateCoordinatorRepository,
        UserRepository $userRepository
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
        $this->securityMailer = $securityMailer;
        $this->requestsMailer = $requestsMailer;
        $this->stateCoordinatorRequestRepository = $stateCoordinatorRequestRepository;
        $this->stateCoordinatorRepository = $stateCoordinatorRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Security("is_granted('ROLE_STATE_COORDINATOR_USER')")
     * @Route("/new", name="regional_coordinator_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newAction(Request $request) {

        /** @var StateCoordinator $user */
        $user = $this->getUser();
        $regionalCoordinator = new RegionalCoordinator();

        $form = $this->createForm(RegionalCoordinatorFormType::class, $regionalCoordinator, [
            'method' => 'POST',
            'state' => $user->getState()
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var RegionalCoordinator $regionalCoordinator */
            $regionalCoordinator = $form->getData();

            $existingUser = $this->userRepository->findOneBy(['email' => $regionalCoordinator->getEmail()]);
            // for now just skip users that are already in the system
            if($existingUser) {
                $this->addFlash('error', 'This user already exists in the system');
                return $this->redirectToRoute('regional_coordinator_new');
            } else {
                $regionalCoordinator->initializeNewUser();
                $regionalCoordinator->setPasswordResetToken();
                $regionalCoordinator->setupAsRegionalCoordinator();
                $this->entityManager->persist($regionalCoordinator);
            }

            $regionalCoordinatorRequest = new RegionalCoordinatorRequest();
            $regionalCoordinatorRequest->setRegion($regionalCoordinator->getRegion());
            $regionalCoordinatorRequest->setNeedsApprovalBy($regionalCoordinator);
            $regionalCoordinatorRequest->setCreatedBy($user);
            $this->entityManager->persist($regionalCoordinatorRequest);
            $this->entityManager->flush();

            $this->securityMailer->sendAccountActivation($regionalCoordinator);
            $this->requestsMailer->regionalCoordinatorRequest($regionalCoordinatorRequest);

            $this->addFlash('success', 'Regional coordinator invite sent.');
            return $this->redirectToRoute('regional_coordinator_new');
        }

        return $this->render('regionalCoordinator/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}