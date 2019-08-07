<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolAdministratorRequest;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Mailer\RequestsMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\NewCompanyRequestRepository;
use App\Repository\RequestRepository;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class RequestController
 * @package App\Controller
 * @Route("/dashboard")
 */
class RequestController extends AbstractController
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
     * @var NewCompanyRequestRepository
     */
    private $newCompanyRequestRepository;

    /**
     * @var JoinCompanyRequestRepository
     */
    private $joinCompanyRequestRepository;

    /**
     * @var RequestRepository
     */
    private $requestRepository;

    /**
     * @var RequestsMailer
     */
    private $requestsMailer;

    /**
     * RequestController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param NewCompanyRequestRepository $newCompanyRequestRepository
     * @param JoinCompanyRequestRepository $joinCompanyRequestRepository
     * @param RequestRepository $requestRepository
     * @param RequestsMailer $requestsMailer
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
        NewCompanyRequestRepository $newCompanyRequestRepository,
        JoinCompanyRequestRepository $joinCompanyRequestRepository,
        RequestRepository $requestRepository,
        RequestsMailer $requestsMailer
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
        $this->newCompanyRequestRepository = $newCompanyRequestRepository;
        $this->joinCompanyRequestRepository = $joinCompanyRequestRepository;
        $this->requestRepository = $requestRepository;
        $this->requestsMailer = $requestsMailer;
    }

    /**
     * @Route("/requests", name="requests", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requests(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $requestsThatNeedMyApproval = $this->requestRepository->findBy([
            'approved' => false,
            'needsApprovalBy' => $user
        ]);

        $myCreatedRequests = $this->requestRepository->findBy([
            'approved' => false,
            'created_by' => $user
        ]);

        // todo you could return a different view per user role as well
        return $this->render('request/index.html.twig', [
            'user' => $user,
            'requestsThatNeedMyApproval' => $requestsThatNeedMyApproval,
            'myCreatedRequests' => $myCreatedRequests
        ]);
    }

    /**
     * @Route("/requests/{id}/approve", name="approve_request", methods={"POST"}, options = { "expose" = true })
     * @param \App\Entity\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function approveRequest(\App\Entity\Request $request) {

        $this->denyAccessUnlessGranted('edit', $request);

        /** @var User $user */
        $user = $this->getUser();

        switch($request->getClassName()) {
            case 'NewCompanyRequest':
                /** @var NewCompanyRequest $request */
                $request->setApproved(true);
                $company = $request->getCompany();
                $company->setApproved(true);
                $this->entityManager->persist($company);
                $this->addFlash('success', 'Company approved');
                $this->requestsMailer->newCompanyRequestApproval($request);
                break;
            case 'JoinCompanyRequest':
                /** @var JoinCompanyRequest $request */
                $request->setApproved(true);
                if($request->getIsFromCompany()) {
                    /** @var ProfessionalUser $needsApprovalBy */
                    $needsApprovalBy = $request->getNeedsApprovalBy();
                    $needsApprovalBy->setupAsProfessional();
                    $needsApprovalBy->setCompany($request->getCompany());
                    $needsApprovalBy->agreeToTerms();
                    $this->entityManager->persist($needsApprovalBy);
                    $this->addFlash('success', 'You have joined the company!');
                } else {
                    /** @var ProfessionalUser $createdBy */
                    $createdBy = $request->getCreatedBy();
                    $createdBy->setupAsProfessional();
                    $createdBy->setCompany($request->getCompany());
                    $createdBy->agreeToTerms();
                    $this->entityManager->persist($createdBy);
                    $this->addFlash('success', 'User successfully added to company!');
                }
                $this->requestsMailer->joinCompanyRequestApproval($request);
                break;
            case 'StateCoordinatorRequest':
                /** @var StateCoordinatorRequest $request */
                $request->setApproved(true);
                /** @var StateCoordinator $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();
                $needsApprovalBy->setupAsStateCoordinator();
                $this->addFlash('success', 'You have accepted a state coordinator position!');
                $needsApprovalBy->setState($request->getState());
                $needsApprovalBy->agreeToTerms();
                $this->entityManager->persist($needsApprovalBy);
                $this->requestsMailer->stateCoordinatorRequestApproval($request);
                break;
            case 'RegionalCoordinatorRequest':
                /** @var RegionalCoordinatorRequest $request */
                $request->setApproved(true);
                /** @var RegionalCoordinator $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();
                $needsApprovalBy->setupAsRegionalCoordinator();
                $this->addFlash('success', 'You have accepted a regional coordinator position!');
                $needsApprovalBy->setRegion($request->getRegion());
                $needsApprovalBy->agreeToTerms();
                $this->entityManager->persist($needsApprovalBy);
                $this->requestsMailer->regionalCoordinatorRequestApproval($request);
                break;

            case 'SchoolAdministratorRequest':
                /** @var SchoolAdministratorRequest $request */
                $request->setApproved(true);
                /** @var SchoolAdministrator $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();
                $needsApprovalBy->setupAsSchoolAdministrator();
                $this->addFlash('success', 'You have accepted a school administrator position!');
                $needsApprovalBy->addSchool($request->getSchool());
                $needsApprovalBy->agreeToTerms();
                $this->entityManager->persist($needsApprovalBy);
                $this->requestsMailer->schoolAdministratorRequestApproval($request);
                break;
        }

        $this->entityManager->persist($request);
        $this->entityManager->flush();
        return $this->redirectToRoute('requests');
    }
}