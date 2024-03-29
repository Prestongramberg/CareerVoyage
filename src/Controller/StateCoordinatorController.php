<?php

namespace App\Controller;

use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class StateCoordinatorController
 * @package App\Controller
 * @Route("/dashboard/
 * state-coordinator")
 */
class StateCoordinatorController extends AbstractController
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
        $this->stateCoordinatorRepository = $stateCoordinatorRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @IsGranted({"ROLE_SITE_ADMIN_USER"})
     * @Route("/new", name="state_coordinator_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newAction(Request $request) {

        /** @var SiteAdminUser $user */
        $user = $this->getUser();
        $stateCoordinator = new StateCoordinator();

        $form = $this->createForm(StateCoordinatorFormType::class, $stateCoordinator, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var StateCoordinator $stateCoordinator */
            $stateCoordinator = $form->getData();

            $existingUser = $this->userRepository->findOneBy(['email' => $stateCoordinator->getEmail()]);
            // for now just skip users that are already in the system
            if($existingUser) {
                $this->addFlash('error', 'This user already exists in the system');
                return $this->redirectToRoute('state_coordinator_new');
            } else {
                $stateCoordinator->initializeNewUser(false, true);
                $stateCoordinator->setPasswordResetToken();
                $stateCoordinator->setupAsStateCoordinator();
                $stateCoordinator->setSite($user->getSite());
                $this->entityManager->persist($stateCoordinator);
            }

            $this->entityManager->flush();
            $this->securityMailer->sendPasswordSetupForStateCoordinator($stateCoordinator);
            $this->addFlash('success', 'State coordinator invite sent.');
            return $this->redirectToRoute('state_coordinator_new');
        }

        return $this->render('stateCoordinator/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}