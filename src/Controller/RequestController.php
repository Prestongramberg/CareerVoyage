<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Mailer\MyRequests\NewCompanyApprovedMailer;
use App\Mailer\MyRequests\RequestFromCompanyToUserToJoinCompanyMailer;
use App\Mailer\MyRequests\RequestFromUserToJoinCompanyMailer;
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
     * @var NewCompanyApprovedMailer
     */
    private $newCompanyApprovedMailer;

    /**
     * @var RequestFromCompanyToUserToJoinCompanyMailer
     */
    private $requestFromCompanyToUserToJoinCompany;

    /**
     * @var RequestFromUserToJoinCompanyMailer
     */
    private $requestFromUserToJoinCompanyMailer;

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
     * @param NewCompanyApprovedMailer $newCompanyApprovedMailer
     * @param RequestFromCompanyToUserToJoinCompanyMailer $requestFromCompanyToUserToJoinCompany
     * @param RequestFromUserToJoinCompanyMailer $requestFromUserToJoinCompanyMailer
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
        NewCompanyApprovedMailer $newCompanyApprovedMailer,
        RequestFromCompanyToUserToJoinCompanyMailer $requestFromCompanyToUserToJoinCompany,
        RequestFromUserToJoinCompanyMailer $requestFromUserToJoinCompanyMailer
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
        $this->newCompanyApprovedMailer = $newCompanyApprovedMailer;
        $this->requestFromCompanyToUserToJoinCompany = $requestFromCompanyToUserToJoinCompany;
        $this->requestFromUserToJoinCompanyMailer = $requestFromUserToJoinCompanyMailer;
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

        $companies = $this->companyRepository->getUnApprovedCompanies();

        $messages = $this->container->get('session')->getFlashBag()->get('message');
        $message = '';
        if(!empty($messages)) {
            $message = $messages[0];
        }

        if($request->getMethod() === 'POST') {

            $request = $request->request->get('request');
            $request = $this->requestRepository->find($request);



            switch($request->getClassName()) {
                case 'NewCompanyRequest':
                    /** @var NewCompanyRequest $request */
                    $request = $this->newCompanyRequestRepository->find($request);
                    $request->setApproved(true);
                    $this->addFlash('message', 'Company approved');
                    $this->newCompanyApprovedMailer->send($request->getCreatedBy(), $request->getCompany());
;                    break;
                case 'JoinCompanyRequest':
                    /** @var JoinCompanyRequestRepository $request */
                    $request = $this->joinCompanyRequestRepository->find($request);
                    $request->setApproved(true);
                    $this->addFlash('message', 'You have joined the company!');
                    $user->setCompany($request->getCompany());
                    $this->entityManager->persist($user);
                    break;
            }

            $this->entityManager->persist($request);
            $this->entityManager->flush();

            return $this->redirectToRoute('requests');
        }

        // todo you could return a different view per user role as well
        return $this->render('request/index.html.twig', [
            'user' => $user,
            'companies' => $companies,
            'requestsThatNeedMyApproval' => $requestsThatNeedMyApproval,
            'myCreatedRequests' => $myCreatedRequests,
            'message' => $message
        ]);
    }

    /**
     * @Route("/requests/companies/{id}/company-join-request", name="company_join_request", methods={"POST"}, options = { "expose" = true })
     * @param Company $company
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyJoinRequest(Company $company, Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $requests = $this->joinCompanyRequestRepository->getJoinCompanyRequestsByCompanyAndUser($company, $user);

        if(count($requests) > 0) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'You have already sent a request to join this company'
                ],
                Response::HTTP_OK
            );
        }

        $joinCompanyRequest = new JoinCompanyRequest();
        $joinCompanyRequest->setCompany($company);
        $joinCompanyRequest->setCreatedBy($user);
        $joinCompanyRequest->setNeedsApprovalBy($company->getOwner());

        $this->entityManager->persist($joinCompanyRequest);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'Request to join company successful'
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/requests/companies/{companyID}/users/{userID}/from-company-join-request", name="from_company_join_request", methods={"POST"}, options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "companyID"})
     * @ParamConverter("user", options={"id" = "userID"})
     * @param Company $company
     * @param User $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fromCompanyJoinRequest(Company $company, User $user, Request $request) {


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

        $this->entityManager->persist($joinCompanyRequest);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'Request for user to join company successful'
            ],
            Response::HTTP_OK
        );
    }

}