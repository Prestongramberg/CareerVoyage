<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\StateCoordinator;
use App\Entity\User;
use App\Factory\RequestFactory;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
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
     * @var RequestFactory
     */
    private $requestFactory;

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
     * @param RequestFactory $requestFactory
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
        RequestsMailer $requestsMailer,
        RequestFactory $requestFactory
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
        $this->requestFactory = $requestFactory;
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

        /** @var User $user */
        $user = $this->getUser();

        switch($request->getClassName()) {
            case 'NewCompanyRequest':
                /** @var NewCompanyRequest $request */
                $request->setApproved(true);
                $this->addFlash('success', 'Company approved');
                $this->requestsMailer->newCompanyApproved($request);
                break;
            case 'JoinCompanyRequest':
                /** @var JoinCompanyRequest $request */
                $request->setApproved(true);
                $this->addFlash('success', 'You have joined the company!');
                $createdBy = $request->getCreatedBy();
                $createdBy->setCompany($request->getCompany());
                $this->entityManager->persist($createdBy);

                if($request->getType() === JoinCompanyRequest::TYPE_COMPANY_TO_USER) {
                    $this->requestsMailer->companyToUserApproval($request);
                } elseif($request->getType() === JoinCompanyRequest::TYPE_USER_TO_COMPANY) {
                    $this->requestsMailer->userToCompanyApproval($request);
                }
                break;
            case 'StateCoordinatorRequest':
                /** @var StateCoordinator $request */
                $request->setApproved(true);
                $this->addFlash('success', 'You have accepted a state coordinator position!');
                $user->setState($request->getState());
                $this->entityManager->persist($user);
                break;
        }

        $this->entityManager->persist($request);
        $this->entityManager->flush();
        return $this->redirectToRoute('requests');
    }

    /**
     * @Route("/requests/companies/{companyID}/users/{userID}/company-to-user", name="company_to_user_request", methods={"POST"}, options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "companyID"})
     * @ParamConverter("user", options={"id" = "userID"})
     * @param Company $company
     * @param User $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyToUserRequest(Company $company, User $user, Request $request) {


        $requests = $this->joinCompanyRequestRepository->getJoinCompanyRequestsFromCompanyByUser($user);

        if(count($requests) > 0) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'You have already sent a request to this user to join this company'
                ],
                Response::HTTP_OK
            );
        }

        $joinCompanyRequest = new JoinCompanyRequest();
        $joinCompanyRequest->setCompany($company);
        $joinCompanyRequest->setCreatedBy($this->getUser());
        $joinCompanyRequest->setNeedsApprovalBy($user);
        $joinCompanyRequest->setType(JoinCompanyRequest::TYPE_COMPANY_TO_USER);

        $this->entityManager->persist($joinCompanyRequest);
        $this->entityManager->flush();

        $this->requestsMailer->companyToUserRequest($joinCompanyRequest);

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'Request for user to join company successful'
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/requests/create", name="requests_create", methods={"POST"}, options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "companyID"})
     * @ParamConverter("user", options={"id" = "userID"})
     * @param Request $request
     */
    public function requestsCreateAction(Request $request) {

        $requestType = $request->request->get('requestType');

        $form = $this->requestFactory->getForm($requestType);

        $name = "Josh";
    }

    public function getRequestFormAction($requestType) {

        return $this->requestFactory->getForm($requestType);
    }

}