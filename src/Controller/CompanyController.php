<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\CompanyVideo;
use App\Entity\CompanyView;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\EducatorRegisterEducatorForCompanyExperienceRequest;
use App\Entity\RequestAction;
use App\Entity\SchoolAdminRegisterSAForCompanyExperienceRequest;
use App\Entity\EducatorUser;
use App\Entity\ExperienceFile;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\Registration;
use App\Entity\RequestPossibleApprovers;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\EditCompanyExperienceType;
use App\Form\EditCompanyFormType;
use App\Form\Filter\CompanyResultsFilterType;
use App\Form\NewCompanyFormType;
use App\Form\NewCompanyExperienceType;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

/**
 * Class ProfileController
 *
 * @package App\Controller
 * @Route("/dashboard")
 */
class CompanyController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/companies", name="company_index", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        return $this->redirectToRoute('company_results_page');

        $user = $this->getUser();

        return $this->render(
            'company/index.html.twig', [
                'user' => $user,
            ]
        );
    }

    /**
     * @Route("/companies/results", name="company_results_page", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyResultsAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(
            CompanyResultsFilterType::class, null, [
                'method' => 'GET',
            ]
        );

        $form->handleRequest($request);

        $useRegionFiltering = false;
        $regions            = [];
        if ($user->isSchoolAdministrator()) {

            $useRegionFiltering = true;

            /** @var SchoolAdministrator $user */
            foreach ($user->getSchools() as $school) {

                if (!$school->getRegion()) {
                    continue;
                }

                $regions[] = $school->getRegion()->getId();
            }
        }

        if ($user->isProfessional()) {

            $useRegionFiltering = true;

            /** @var ProfessionalUser $user */

            foreach ($user->getRegions() as $region) {

                $regions[] = $region->getId();
            }
        }

        if ($user->isStudent() || $user->isEducator()) {

            $useRegionFiltering = true;

            /** @var StudentUser|EducatorUser $user */

            if ($user->getSchool() && $user->getSchool()->getRegion()) {
                $regions[] = $user->getSchool()->getRegion()->getId();
            }
        }

        $regions = array_unique($regions);

        if ($useRegionFiltering) {
            $filterBuilder = $this->companyRepository->createQueryBuilder('c')
                                                     ->leftJoin('c.regions', 'regions')
                                                     ->andWhere('regions.id IN (:regions)')
                                                     ->andWhere('c.deleted = 0')
                                                     ->setParameter('regions', $regions)
                                                     ->addOrderBy('c.name', 'ASC');
        } else {

            $filterBuilder = $this->companyRepository->createQueryBuilder('c')
                                                     ->andWhere('c.deleted = 0')
                                                     ->addOrderBy('c.name', 'ASC');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $sql = $filterQuery->getSQL();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render(
            'company/results.html.twig', [
                'user' => $user,
                'pagination' => $pagination,
                'form' => $form->createView(),
                'zipcode' => $request->query->get('zipcode', ''),
                'clearFormUrl' => $this->generateUrl('company_results_page'),
            ]
        );
    }

    /**
     * @Route("/companies/favorites", name="company_favorites_page", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyFavoritesAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(
            CompanyResultsFilterType::class, null, [
                'method' => 'GET',
            ]
        );

        $form->handleRequest($request);

        $filterBuilder = $this->companyRepository->createQueryBuilder('c')
                                                 ->innerJoin('c.companyFavorites', 'cf')
                                                 ->innerJoin('cf.user', 'u')
                                                 ->andWhere('u.id = :userId')
                                                 ->andWhere('c.deleted = 0')
                                                 ->setParameter('userId', $user->getId())
                                                 ->addOrderBy('c.name', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $sql = $filterQuery->getSQL();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render(
            'company/my_favorites.html.twig', [
                'user' => $user,
                'pagination' => $pagination,
                'form' => $form->createView(),
                'zipcode' => $request->query->get('zipcode', ''),
                'clearFormUrl' => $this->generateUrl('company_favorites_page'),
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_PROFESSIONAL_USER')")
     *
     * @Route("/companies/mine", name="company_mine_page", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyMineAction(Request $request)
    {

        /** @var ProfessionalUser $user */
        $user = $this->getUser();

        return $this->render(
            'company/mine.html.twig', [
                'user' => $user,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_PROFESSIONAL_USER")
     *
     * @Route("/companies/new", name="company_new", options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        // if user already has company created then don't let them create another
        if ($user->getCompany() && $user->getCompany()->getOwner() && $user->getCompany()->getOwner()->getId() === $user->getId()) {
            return $this->redirectToRoute('company_view', ['id' => $user->getCompany()->getId()]);
        }

        $company = new Company();

        $options = [
            'method' => 'POST',
            'company' => $company,
            'skip_validation' => $request->request->get('skip_validation', false),
        ];

        $form = $this->createForm(NewCompanyFormType::class, $company, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Company $company */
            $company              = $form->getData();
            $shouldAttemptGeocode = $company->getStreet() && $company->getCity() && $company->getState() && $company->getZipcode();
            if ($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($company->getFormattedAddress())) {
                $company->setLongitude($coordinates['lng']);
                $company->setLatitude($coordinates['lat']);
            }
            $company->setOwner($user);
            $user->setCompany($company);
            $user->addRole(User::ROLE_COMPANY_ADMINISTRATOR);
            $adminUsers = $this->adminUserRepository->findAll();
            $adminUser  = $adminUsers[0];

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            // create a new company request
            $newCompanyRequest = new \App\Entity\Request();
            $newCompanyRequest->setRequestType(\App\Entity\Request::REQUEST_TYPE_NEW_COMPANY);
            $newCompanyRequest->setCreatedBy($user);
            $newCompanyRequest->addNeedsApprovalByRole(User::ROLE_ADMIN_USER);
            $newCompanyRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING);
            $newCompanyRequest->setStatusLabel('Company Pending Approval');
            $newCompanyRequest->setNotification([
                'title' => "<strong>{$user->getFullName()}</strong> has created a new company {$company->getName()}",
                'user_photo' => $user->getPhotoPath(),
                'user_photos' => [],
                'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
                'messages' => [],
                'body' => [
                    'Request Type' => [
                        'order' => 1,
                        'value' => 'New Company',
                    ],
                    'Initiated By' => [
                        'order' => 2,
                        'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $user->getId()])}'>{$user->getFullName()}</a>",
                    ],
                    'Company Name' => [
                        'order' => 3,
                        'value' => "<a target='_blank' href='{$this->generateUrl('company_view', ['id' => $company->getId()])}'>{$company->getName()}</a>",
                    ],
                    'Website' => [
                        'order' => 4,
                        'value' => "<a target='_blank' href='{$company->getWebsite()}'>{$company->getWebsite()}</a>",

                    ],
                    'Phone' => [
                        'order' => 5,
                        'value' => $company->getPhone(),
                    ],
                    'Created On' => [
                        'order' => 6,
                        'value' => (new \DateTime())->format("m/d/Y h:i A"),
                    ],
                ],
            ]);

            $createdByApprover = new RequestPossibleApprovers();
            $createdByApprover->setPossibleApprover($user);
            $createdByApprover->setRequest($newCompanyRequest);
            $createdByApprover->setNotificationDate(new \DateTime());
            $createdByApprover->setPossibleActions([
                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
            ]);
            $createdByApprover->setNotificationTitle("<strong>You</strong> have created a new company {$company->getName()}");
            $this->entityManager->persist($createdByApprover);

            $adminUsers = $this->userRepository->findByRole(User::ROLE_ADMIN_USER);
            foreach ($adminUsers as $adminUser) {
                $possibleApprover = new RequestPossibleApprovers();
                $possibleApprover->setPossibleActions([
                    RequestAction::REQUEST_ACTION_NAME_APPROVE,
                    RequestAction::REQUEST_ACTION_NAME_DENY,
                    RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                    RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
                ]);
                $possibleApprover->setNotificationTitle("<strong>{$user->getFullName()}</strong> has created a new company {$company->getName()}");
                $possibleApprover->setPossibleApprover($adminUser);
                $possibleApprover->setHasNotification(true);
                $possibleApprover->setRequest($newCompanyRequest);
                $this->entityManager->persist($possibleApprover);
            }

            $this->entityManager->persist($newCompanyRequest);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $requestActionUrl = $this->generateUrl('request_action', [
                'company_id' => $company->getId(),
                'request_id' => $newCompanyRequest->getId(),
            ]);

            $newCompanyRequest->setActionUrl($requestActionUrl);

            $this->entityManager->flush();
            $this->entityManager->refresh($newCompanyRequest);

            $this->requestsMailer->newCompanyApproval($newCompanyRequest, $company);
            $this->requestsMailer->newCompanyAwaitingApproval($newCompanyRequest, $company);

            $this->addFlash('success', 'Company successfully created. While your company is waiting for approval go ahead and add some images and videos!');

            return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);

        }

        if ($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView(
                        'api/form/secondary_industry_form_new_company_field.html.twig', [
                            'form' => $form->createView(),
                        ]
                    ),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return $this->render(
            'company/new.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/companies/{id}/view", name="company_view", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function viewAction(Request $request, Company $company)
    {

        $user                = $this->getUser();
        $professional_users  = $this->companyRepository->getActiveProfessionalUsers($company->getId());
        $company_experiences = $this->companyExperienceRepository->findBy(['cancelled' => 0]);

        // Create company_view record for the user.
        $userViews = $this->getDoctrine()->getRepository(CompanyView::class)->getLastCompanyView($company->getId(), $user->getId());
        if (sizeof($userViews) == 0) {
            $entityManager = $this->getDoctrine()->getManager();
            $view          = new CompanyView();
            $view->setCompany($company);
            $view->setUser($user);
            $view->setCreatedAt(new DateTime());
            $entityManager->persist($view);
            $entityManager->flush();
        } else {
            $dt = new DateTime();
            if ($userViews[0]->getCreatedAt() <= $dt->modify("-1 day")) {
                $entityManager = $this->getDoctrine()->getManager();
                $view          = new CompanyView();
                $view->setCompany($company);
                $view->setUser($user);
                $view->setCreatedAt(new DateTime());
                $entityManager->persist($view);
                $entityManager->flush();
            }
        }


        return $this->render(
            'company/view.html.twig', [
                'user' => $user,
                'company' => $company,
                'professionalUsers' => $professional_users,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_PROFESSIONAL_USER")
     * @Route("/companies/{id}/join", name="company_join", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function joinAction(Request $request, Company $company)
    {

        /** @var User $user */
        $user = $this->getUser();

        // create a new company request
        $joinCompanyRequest = new \App\Entity\Request();
        $joinCompanyRequest->setRequestType(\App\Entity\Request::REQUEST_TYPE_JOIN_COMPANY);
        $joinCompanyRequest->setCreatedBy($user);
        $joinCompanyRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING);
        $joinCompanyRequest->setStatusLabel('Join Company Pending Approval');
        $joinCompanyRequest->setNotification([
            'title' => "<strong>{$user->getFullName()}</strong> has requested to join your company {$company->getName()}",
            'user_photo' => $user->getPhotoPath(),
            'user_photos' => [],
            'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
            'messages' => [],
            'body' => [
                'Request Type' => [
                    'order' => 1,
                    'value' => 'Join Company',
                ],
                'Initiated By' => [
                    'order' => 2,
                    'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $user->getId()])}'>{$user->getFullName()}</a>",
                ],
                'Company Name' => [
                    'order' => 3,
                    'value' => "<a target='_blank' href='{$this->generateUrl('company_view', ['id' => $company->getId()])}'>{$company->getName()}</a>",
                ],
                'Website' => [
                    'order' => 4,
                    'value' => "<a target='_blank' href='{$company->getWebsite()}'>{$company->getWebsite()}</a>",

                ],
                'Phone' => [
                    'order' => 5,
                    'value' => $company->getPhone(),
                ],
                'Created On' => [
                    'order' => 6,
                    'value' => (new \DateTime())->format("m/d/Y h:i A"),
                ],
            ],
        ]);

        $createdByApprover = new RequestPossibleApprovers();
        $createdByApprover->setPossibleApprover($user);
        $createdByApprover->setRequest($joinCompanyRequest);
        $createdByApprover->setNotificationDate(new \DateTime());
        $createdByApprover->setPossibleActions([
            RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
        ]);
        $createdByApprover->setNotificationTitle("You have requested to join company <strong>{$company->getName()}</strong>");
        $this->entityManager->persist($createdByApprover);


        $possibleApprover = new RequestPossibleApprovers();
        $possibleApprover->setPossibleApprover($company->getOwner());
        $possibleApprover->setNotificationTitle("<strong>{$user->getFullName()}</strong> has requested to join your company {$company->getName()}");
        $possibleApprover->setRequest($joinCompanyRequest);
        $possibleApprover->setHasNotification(true);
        $possibleApprover->setPossibleActions(
            [
                RequestAction::REQUEST_ACTION_NAME_APPROVE,
                RequestAction::REQUEST_ACTION_NAME_DENY,
                RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
            ]
        );
        $this->entityManager->persist($possibleApprover);

        $this->entityManager->persist($joinCompanyRequest);
        $this->entityManager->flush();

        $requestActionUrl = $this->generateUrl('request_action', [
            'company_id' => $company->getId(),
            'request_id' => $joinCompanyRequest->getId(),
        ]);

        $joinCompanyRequest->setActionUrl($requestActionUrl);

        $this->entityManager->flush();
        $this->entityManager->refresh($joinCompanyRequest);

        $this->requestsMailer->joinCompanyApproval($joinCompanyRequest, $company);

        $this->addFlash('success', 'Request to join this company has been sent to the company administrator');

        return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
    }

    /**
     * @Route("/companies/{company}/users/{professionalUser}/invite", name="company_invite", options = { "expose" = true }, methods={"GET", "POST"})
     * @param Request          $request
     * @param Company          $company
     *
     * @param ProfessionalUser $professionalUser
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function inviteAction(Request $request, Company $company, ProfessionalUser $professionalUser)
    {

        $this->denyAccessUnlessGranted('edit', $company);

        /** @var User $user */
        $user = $this->getUser();

        // create a new company request
        $companyInviteRequest = new \App\Entity\Request();
        $companyInviteRequest->setRequestType(\App\Entity\Request::REQUEST_TYPE_COMPANY_INVITE);
        $companyInviteRequest->setCreatedBy($user);
        $companyInviteRequest->setStatus(\App\Entity\Request::REQUEST_STATUS_PENDING);
        $companyInviteRequest->setStatusLabel('Company invite is pending approval');
        $companyInviteRequest->setNotification([
            'title' => "<strong>{$user->getFullName()}</strong> has invited you to join their company {$company->getName()}",
            'user_photo' => $user->getPhotoPath(),
            'user_photos' => [],
            'created_on' => (new \DateTime())->format("m/d/Y h:i:s A"),
            'body' => [
                'Request Type' => [
                    'order' => 1,
                    'value' => 'Company Invite',
                ],
                'Initiated By' => [
                    'order' => 2,
                    'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $user->getId()])}'>{$user->getFullName()}</a>",
                ],
                'Sent To' => [
                    'order' => 3,
                    'value' => "<a target='_blank' href='{$this->generateUrl('profile_index', ['id' => $professionalUser->getId()])}'>{$professionalUser->getFullName()}</a>",
                ],
                'Company Name' => [
                    'order' => 4,
                    'value' => "<a target='_blank' href='{$this->generateUrl('company_view', ['id' => $company->getId()])}'>{$company->getName()}</a>",
                ],
                'Website' => [
                    'order' => 5,
                    'value' => "<a target='_blank' href='{$company->getWebsite()}'>{$company->getWebsite()}</a>",

                ],
                'Phone' => [
                    'order' => 6,
                    'value' => $company->getPhone(),
                ],
                'Created On' => [
                    'order' => 7,
                    'value' => (new \DateTime())->format("m/d/Y h:i A"),
                ],
            ],
        ]);

        $createdByApprover = new RequestPossibleApprovers();
        $createdByApprover->setPossibleApprover($user);
        $createdByApprover->setRequest($companyInviteRequest);
        $createdByApprover->setNotificationDate(new \DateTime());
        $createdByApprover->setPossibleActions([
            RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
        ]);
        $createdByApprover->setNotificationTitle("You have invited <strong>{$professionalUser->getFullName()}</strong> to join your company {$company->getName()}");
        $this->entityManager->persist($createdByApprover);

        $possibleApprover = new RequestPossibleApprovers();
        $possibleApprover->setPossibleApprover($professionalUser);
        $possibleApprover->setRequest($companyInviteRequest);
        $possibleApprover->setHasNotification(true);
        $possibleApprover->setPossibleActions(
            [
                RequestAction::REQUEST_ACTION_NAME_APPROVE,
                RequestAction::REQUEST_ACTION_NAME_DENY,
                RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING,
                RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE,
            ]
        );
        $possibleApprover->setNotificationTitle("<strong>{$user->getFullName()}</strong> has invited you to join their company {$company->getName()}");
        $this->entityManager->persist($possibleApprover);

        $this->entityManager->persist($companyInviteRequest);
        $this->entityManager->flush();

        $requestActionUrl = $this->generateUrl('request_action', [
            'company_id' => $company->getId(),
            'request_id' => $companyInviteRequest->getId(),
        ]);

        $companyInviteRequest->setActionUrl($requestActionUrl);

        $this->entityManager->flush();
        $this->entityManager->refresh($companyInviteRequest);

        $this->requestsMailer->companyInviteApproval($companyInviteRequest, $company);

        $this->addFlash('success', 'Request successfully sent!');

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * @Route("/companies/{id}/professionals", name="company_professionals", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyProfessionalsAction(Request $request, Company $company)
    {

        $professionals = $this->professionalUserRepository->findBy(
            [
                'company' => $company->getId(),
            ]
        );

        /** @var User $user */
        $user = $this->getUser();

        return $this->render(
            'company/professionals.html.twig', [
                'user' => $user,
                'company' => $company,
                'professionals' => $professionals,
            ]
        );
    }

    /**
     * @Route("/companies/professionals/{id}/remove", name="company_remove_user", options = { "expose" = true }, methods={"POST"})
     * @param Request          $request
     * @param ProfessionalUser $professional
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyProfessionalRemoveAction(Request $request, ProfessionalUser $professional)
    {

        $company = $professional->getCompany();
        /** @var User $user */
        $user      = $this->getUser();
        $canRemove = false;

        if ($user->isAdmin()) {
            $canRemove = true;
        } else {
            if ($company->isUserOwner($professional)) {
                // the owner of the company can't be removed unless someone else becomes the owner first
                $canRemove = false;
            } else {
                if ($user->getId() != $professional->getId()) {
                    // Cannot remove yourself from a company
                    $canRemove = true;
                }
            }
        }

        if (!$canRemove) {
            $this->addFlash('error', 'That user cannot be removed from the company.');

            return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
        }

        $companyId = $professional->getCompany()->getId();
        $professional->setCompany(null);
        $this->entityManager->persist($professional);
        $this->entityManager->flush();

        $this->addFlash('success', 'Professional removed from company');

        return $this->redirectToRoute('company_view', ['id' => $companyId]);
    }

    /**
     * @Route("/companies/{id}/thumbnail/add", name="company_thumbnail_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function companyAddThumbnailAction(Request $request, Company $company)
    {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $thumbnailImage = $request->files->get('file');

        if ($thumbnailImage) {
            $mimeType    = $thumbnailImage->getMimeType();
            $newFilename = $this->uploaderHelper->upload($thumbnailImage, UploaderHelper::THUMBNAIL_IMAGE);
            $image       = new Image();
            $image->setOriginalName($thumbnailImage->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $company->setThumbnailImage($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::THUMBNAIL_IMAGE) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::THUMBNAIL_IMAGE . '/' . $newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/{id}/featured/add", name="company_featured_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function companyAddFeaturedAction(Request $request, Company $company)
    {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $featuredImage = $request->files->get('file');

        if ($featuredImage) {
            $mimeType    = $featuredImage->getMimeType();
            $newFilename = $this->uploaderHelper->upload($featuredImage, UploaderHelper::FEATURE_IMAGE);
            $image       = new Image();
            $image->setOriginalName($featuredImage->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $company->setFeaturedImage($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::FEATURE_IMAGE) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::FEATURE_IMAGE . '/' . $newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/{id}/resource/add", name="company_resource_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function companyAddResourceAction(Request $request, Company $company)
    {

        $this->denyAccessUnlessGranted('edit', $company);

        /** @var UploadedFile $file */
        $file          = $request->files->get('resource');
        $title         = $request->request->get('title');
        $linkToWebsite = $request->request->get('linkToWebsite');
        $description   = $request->request->get('description');

        if (!$file && !$linkToWebsite) {
            return new JsonResponse(
                [
                    'success' => false,

                ], Response::HTTP_BAD_REQUEST
            );
        }

        if (!$title) {
            return new JsonResponse(
                [
                    'success' => false,

                ], Response::HTTP_BAD_REQUEST
            );
        }

        $companyResource = new CompanyResource();

        if ($file) {
            $mimeType    = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::COMPANY_RESOURCE);
            $companyResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $companyResource->setMimeType($mimeType ?? 'application/octet-stream');
            $companyResource->setFileName($newFilename);
            $companyResource->setFile(null);
        }

        if ($linkToWebsite) {
            $companyResource->setLinkToWebsite($linkToWebsite);
        }

        $companyResource->setCompany($company);
        $companyResource->setDescription($description ? $description : null);
        $companyResource->setTitle($title);
        $this->entityManager->persist($companyResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'url' => $file ? $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::COMPANY_RESOURCE . '/' . $newFilename : $companyResource->getLinkToWebsite(),
                'id' => $companyResource->getId(),
                'title' => $title,
                'description' => $description,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/resource/{id}/get", name="company_resource_get", options = { "expose" = true })
     * @param Request         $request
     * @param CompanyResource $companyResource
     *
     * @return JsonResponse
     */
    public function companyGetResourceAction(Request $request, CompanyResource $companyResource)
    {

        $this->denyAccessUnlessGranted('edit', $companyResource->getCompany());

        if ($companyResource->getFile() != null) {
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::EXPERIENCE_FILE . '/' . $companyResource->getFileName(),
                    'id' => $companyResource->getId(),
                    'title' => $companyResource->getTitle(),
                    'description' => $companyResource->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success' => true,
                    'website' => $companyResource->getLinkToWebsite(),
                    'id' => $companyResource->getId(),
                    'title' => $companyResource->getTitle(),
                    'description' => $companyResource->getDescription(),

                ], Response::HTTP_OK
            );
        }
    }

    /**
     * @Route("/companies/resource/{id}/edit", name="company_resource_edit", options = { "expose" = true })
     * @param Request         $request
     * @param CompanyResource $companyResource
     *
     * @return JsonResponse
     */
    public function companyEditResourceAction(Request $request, CompanyResource $file)
    {

        $this->denyAccessUnlessGranted('edit', $file->getCompany());

        /** @var UploadedFile $resource */
        $resource      = $request->files->get('resource');
        $title         = $request->request->get('title');
        $description   = $request->request->get('description');
        $linkToWebsite = $request->request->get('linkToWebsite');

        if ($title) {
            $file->setTitle($title);
        }

        if ($description) {
            $file->setDescription($description);
        }

        if ($linkToWebsite && $linkToWebsite != "http://") {
            $file->setLinkToWebsite($linkToWebsite);
        } else {
            $file->setLinkToWebsite(null);
        }

        if ($resource) {
            $mimeType    = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::EXPERIENCE_FILE);
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
        } else {
            $file->setOriginalName(null);
            $file->setMimeType(null);
            $file->setFileName(null);
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        if ($file->getFileName() != null) {
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::EXPERIENCE_FILE . '/' . $file->getFileName(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $file->getLinkToWebsite(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        }

        // /** @var UploadedFile $file */
        // $file = $request->files->get('resource');
        // $title = $request->request->get('title');
        // $description = $request->request->get('description');

        // if($file && $title && $description) {
        //     $mimeType = $file->getMimeType();
        //     $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::COMPANY_RESOURCE);
        //     $companyResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
        //     $companyResource->setMimeType($mimeType ?? 'application/octet-stream');
        //     $companyResource->setFileName($newFilename);
        //     $companyResource->setFile(null);
        //     $companyResource->setDescription($description);
        //     $companyResource->setTitle($title);
        //     $this->entityManager->persist($companyResource);
        //     $this->entityManager->flush();

        //     return new JsonResponse(
        //         [
        //             'success' => true,
        //             'url' => 'uploads/'.UploaderHelper::COMPANY_RESOURCE.'/'.$newFilename,
        //             'id' => $companyResource->getId(),
        //             'title' => $title,
        //             'description' => $description,

        //         ], Response::HTTP_OK
        //     );
        // }

        // return new JsonResponse(
        //     [
        //         'success' => false,

        //     ], Response::HTTP_BAD_REQUEST
        // );
    }


    /**
     * @Route("/companies/videos/{id}/edit", name="company_video_edit", options = { "expose" = true })
     * @param Request      $request
     * @param CompanyVideo $video
     *
     * @return JsonResponse
     */
    public function companyEditVideoAction(Request $request, CompanyVideo $video)
    {

        $this->denyAccessUnlessGranted('edit', $video->getCompany());

        $name    = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags    = $request->request->get('tags');

        if ($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);

            if ($tags) {
                $video->setTags($tags);
            }


            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{id}/video/add", name="company_video_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function companyAddVideoAction(Request $request, Company $company)
    {

        $this->denyAccessUnlessGranted('edit', $company);

        $name    = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags    = $request->request->get('tags');

        if ($name && $videoId) {
            $video = new CompanyVideo();
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setCompany($company);

            if ($tags) {
                $video->setTags($tags);
            }

            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/videos/{id}/remove", name="company_video_remove", options = { "expose" = true })
     * @param Request      $request
     * @param CompanyVideo $companyVideo
     *
     * @return JsonResponse
     */
    public function companyRemoveVideoAction(Request $request, CompanyVideo $companyVideo)
    {

        $this->denyAccessUnlessGranted('edit', $companyVideo->getCompany());

        $this->entityManager->remove($companyVideo);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }


    /**
     * @Route("/companies/resource/{id}/remove", name="company_resource_remove", options = { "expose" = true })
     * @param Request         $request
     * @param CompanyResource $companyResource
     *
     * @return JsonResponse
     */
    public function companyRemoveResourceAction(Request $request, CompanyResource $companyResource)
    {

        $this->denyAccessUnlessGranted('edit', $companyResource->getCompany());

        $this->entityManager->remove($companyResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{id}/photos/add", name="company_photos_add", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function companyAddPhotosAction(Request $request, Company $company)
    {

        $this->denyAccessUnlessGranted('edit', $company);

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $photo = $request->files->get('file');

        if ($photo) {
            $mimeType    = $photo->getMimeType();
            $newFilename = $this->uploaderHelper->upload($photo, UploaderHelper::COMPANY_PHOTO);
            $image       = new CompanyPhoto();
            $image->setOriginalName($photo->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $company->addCompanyPhoto($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::COMPANY_PHOTO) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::COMPANY_PHOTO . '/' . $newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/companies/photos/{id}/remove", name="company_photo_remove", options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "company_id"})
     * @ParamConverter("companyResource", options={"id" = "photo_id"})
     * @param Request      $request
     * @param CompanyPhoto $companyPhoto
     *
     * @return JsonResponse
     */
    public function companyRemovePhotoAction(Request $request, CompanyPhoto $companyPhoto)
    {

        $this->denyAccessUnlessGranted('edit', $companyPhoto->getCompany());

        $this->entityManager->remove($companyPhoto);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{id}/edit", name="company_edit", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Company $company)
    {

        $this->denyAccessUnlessGranted('edit', $company);

        $editVideoId  = $request->query->get('videoEdit', null);
        $companyVideo = null;
        if ($editVideoId) {
            $companyVideo = $this->videoRepository->find($editVideoId);
        }

        $user = $this->getUser();

        $options = [
            'method' => 'POST',
            'company' => $company,
            'skip_validation' => $request->request->get('skip_validation', false),
        ];

        $form = $this->createForm(EditCompanyFormType::class, $company, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Company $company */
            $company              = $form->getData();
            $shouldAttemptGeocode = $company->getStreet() && $company->getCity() && $company->getState() && $company->getZipcode();
            if ($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($company->getFormattedAddress())) {
                $company->setLongitude($coordinates['lng']);
                $company->setLatitude($coordinates['lat']);
            }
            $this->entityManager->persist($company);
            $this->entityManager->flush();

            $this->addFlash('success', 'Company successfully updated');

            if ($company->getOwner() == $this->get('security.token_storage')->getToken()->getUser()) {
                return $this->redirectToRoute('company_edit', ['id' => $company->getId()]);
            } else {
                return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
            }
        }
        if ($form->isSubmitted() && !$form->isValid() && !$request->request->has('primary_industry_change')) {

            $errors = $this->getFormErrors($form);

            $showMainError = true;
            foreach ($errors as $fieldName => $error) {

                if ($fieldName === 'secondaryIndustries') {
                    $showMainError = false;
                    $this->addFlash('error', 'Please choose at least one career field.');
                }

                if ($fieldName === 'schools') {
                    $showMainError = false;
                    $this->addFlash('error', 'Please select your volunteer schools or try selecting a different region if you do not see the schools you are looking for below.');
                }

                if ($showMainError) {
                    $this->addFlash('error', 'Company was not updated. Please check all tabs for required information.');
                }

            }
        }

        if ($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView(
                        'api/form/secondary_industry_form_field.html.twig', [
                            'form' => $form->createView(),
                        ]
                    ),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return $this->render(
            'company/edit.html.twig', [
                'company' => $company,
                'form' => $form->createView(),
                'user' => $user,
                'companyVideo' => $companyVideo,
            ]
        );
    }

    /**
     * @Route("/companies/{id}/delete", name="company_delete", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function deleteCompanyAction(Company $company, Request $request)
    {

        $this->denyAccessUnlessGranted('edit', $company);

        /** @var User $user */
        $user = $this->getUser();

        $this->entityManager->remove($company);
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            return new JsonResponse(["status" => "success"]);
        } else {
            $this->addFlash('success', 'Company deleted');

            return $this->redirectToRoute('company_index');
        }
    }

    /**
     * @Route("/companies/{id}/experiences/create", name="company_experience_create", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createExperienceAction(Request $request, Company $company)
    {

        $this->denyAccessUnlessGranted('edit', $company);

        $user       = $loggedInUser = $this->getUser();
        $experience = new CompanyExperience();

        $form = $this->createForm(
            NewCompanyExperienceType::class, $experience, [
                'method' => 'POST',
                'company' => $company,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CompanyExperience $experience */
            $experience = $form->getData();

            $shouldAttemptGeocode = $experience->getStreet() && $experience->getCity() && $experience->getState() && $experience->getZipcode();
            if ($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($experience->getFormattedAddress())) {
                $experience->setLongitude($coordinates['lng']);
                $experience->setLatitude($coordinates['lat']);
            }

            $this->entityManager->persist($experience);

            $experience->setCompany($company);

            $registration = new Registration();
            $registration->setUser($this->getUser());
            $registration->setExperience($experience);

            $this->entityManager->persist($registration);

            $this->entityManager->flush();

            $regions = [];
            if ($loggedInUser->isProfessional()) {
                /** @var ProfessionalUser $loggedInUser */
                foreach ($loggedInUser->getRegions() as $region) {

                    $regions[] = $region->getId();
                }
            }

            $regions = array_unique($regions);

            if ($request->request->get('notify_students') === "match") {
                // Send email to students that are interested in the event that was created
                $items        = $experience->getSecondaryIndustries();
                $loggedInUser = $this->getUser();

                $chosen_students = [];
                foreach ($items as $k => $v) {
                    $students = $this->studentUserRepository->findStudentBySecondaryIndustry($v->getId());
                    /** @var StudentUser $student */
                    foreach ($students as $student) {
                        $chosen_students[$student->getId()] = $student;
                    }
                }
            }
            if ($request->request->get('notify_students') === "all") {
                $chosen_students = [];
                $students        = $this->studentUserRepository->createQueryBuilder('s')
                                                               ->setMaxResults(20)
                                                               ->getQuery()
                                                               ->getResult();
                /** @var StudentUser $student */
                foreach ($students as $student) {
                    $chosen_students[$student->getId()] = $student;
                }
            }

            if ($request->request->get('notify_students') !== "none") {

                // Choose teachers who match this profession here.
                $items           = $experience->getSecondaryIndustries();
                $chosen_teachers = [];
                foreach ($items as $k => $v) {
                    $teachers = $this->educatorUserRepository->findEducatorBySecondaryIndustry($v->getId());
                    /** @var EducatorUser $teacher */
                    foreach ($teachers as $teacher) {
                        $chosen_teachers[$teacher->getId()] = $teacher;
                    }
                }

                $message = $request->get('message', '');
                $message = sprintf("Event: %s Message: %s", $experience->getTitle(), $message);

                /** @var StudentUser $student */
                foreach ($chosen_students as $student) {

                    if (!empty($student->getSchool()->getRegion()->getId()) && in_array($student->getSchool()->getRegion()->getId(), $regions)) {
                        $this->experienceMailer->experienceForward($experience, $student, $message, $loggedInUser);
                    }
                }

                /** @var EducatorUser $teacher */
                foreach ($chosen_teachers as $teacher) {

                    if (!empty($teacher->getSchool()->getRegion()->getId()) && in_array($teacher->getSchool()->getRegion()->getId(), $regions)) {
                        $this->experienceMailer->experienceForward($experience, $teacher, $message, $loggedInUser);
                    }
                }
            }

            $this->addFlash('success', 'Experience successfully created!');

            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }


        $secondaryIndustries = $form->get('secondaryIndustries')->getData();

        if (!empty($secondaryIndustries)) {

            return $this->render(
                'company/new_experience.html.twig', [
                    'company' => $company,
                    'form' => $form->createView(),
                    'user' => $user,
                    'secondaryIndustries' => $secondaryIndustries,
                ]
            );
        } else {
            return $this->render(
                'company/new_experience.html.twig', [
                    'company' => $company,
                    'form' => $form->createView(),
                    'user' => $user,
                    'secondaryIndustries' => null,
                ]
            );
        }
    }

    /**
     * @Route("/companies/experiences/{id}/edit", name="company_experience_edit", options = { "expose" = true })
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editExperienceAction(Request $request, CompanyExperience $experience)
    {

        $this->denyAccessUnlessGranted('edit', $experience->getCompany());

        $company = $experience->getCompany();

        $user = $this->getUser();

        $form = $this->createForm(
            EditCompanyExperienceType::class, $experience, [
                'method' => 'POST',
                'company' => $company,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CompanyExperience $experience */
            $experience = $form->getData();

            $shouldAttemptGeocode = $experience->getStreet() && $experience->getCity() && $experience->getState() && $experience->getZipcode();
            if ($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($experience->getFormattedAddress())) {
                $experience->setLongitude($coordinates['lng']);
                $experience->setLatitude($coordinates['lat']);
            }

            $this->entityManager->persist($experience);
            $experience->setCompany($company);
            $this->entityManager->flush();

            if ($request->request->get('notify_students') === "match") {
                // Send email to students that are interested in the event that was created
                $items        = $experience->getSecondaryIndustries();
                $loggedInUser = $this->getUser();

                $chosen_students = [];
                foreach ($items as $k => $v) {
                    $students = $this->studentUserRepository->findStudentBySecondaryIndustry($v->getId());
                    /** @var StudentUser $student */
                    foreach ($students as $student) {
                        $chosen_students[$student->getId()] = $student;
                    }
                }
            }
            if ($request->request->get('notify_students') === "all") {
                $chosen_students = [];
                $students        = $this->studentUserRepository->createQueryBuilder('s')
                                                               ->setMaxResults(20)
                                                               ->getQuery()
                                                               ->getResult();
                /** @var StudentUser $student */
                foreach ($students as $student) {
                    $chosen_students[$student->getId()] = $student;
                }
            }

            if ($request->request->get('notify_students') !== "none") {

                // Choose teachers who match this profession here.
                $items           = $experience->getSecondaryIndustries();
                $chosen_teachers = [];
                foreach ($items as $k => $v) {
                    $teachers = $this->educatorUserRepository->findEducatorBySecondaryIndustry($v->getId());
                    /** @var EducatorUser $teacher */
                    foreach ($teachers as $teacher) {
                        $chosen_teachers[$teacher->getId()] = $teacher;
                    }
                }

                $message = $request->get('message', '');
                $message = sprintf("Event: %s Message: %s", $experience->getTitle(), $message);

                /** @var StudentUser $student */
                foreach ($chosen_students as $student) {
                    $this->experienceMailer->experienceForward($experience, $student, $message, $loggedInUser);
                }

                /** @var EducatorUser $teacher */
                foreach ($chosen_teachers as $teacher) {
                    $this->experienceMailer->experienceForward($experience, $teacher, $message, $loggedInUser);
                }
            }

            $this->addFlash('success', 'Experience successfully updated!');

            return $this->redirectToRoute('company_experience_edit', ['id' => $experience->getId()]);
        }

        return $this->render(
            'company/edit_experience.html.twig', [
                'company' => $company,
                'form' => $form->createView(),
                'user' => $user,
                'experience' => $experience,
            ]
        );
    }


    /**
     * @Route("/companies/experiences/{id}/view", name="company_experience_view", options = { "expose" = true })
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewExperienceAction(Request $request, CompanyExperience $experience)
    {

        /** @var User $user */
        $user = $this->getUser();

        return $this->render(
            'company/view_experience.html.twig', [
                'user' => $user,
                'experience' => $experience,
            ]
        );
    }

    /**
     * @Route("/companies/experiences/{id}/data", name="company_experience_data", options = { "expose" = true })
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dataExperienceAction(Request $request, CompanyExperience $experience)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($experience->getEmployeeContact() && $user->getId() === $experience->getEmployeeContact()->getId()) {
            return new JsonResponse(['user_id' => $experience->getEmployeeContact()->getId(), 'allow_edit' => true]);
        } else {
            return new JsonResponse(['user_id' => $experience->getEmployeeContact()->getId(), 'allow_edit' => false]);
        }
    }


    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/companies/experiences/{id}/educator/register", name="company_experience_educator_register", options = { "expose" = true }, methods={"POST"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyExperienceEducatorRegisterAction(Request $request, CompanyExperience $experience)
    {

        $educatorIdToRegister = $request->request->get('educatorId');
        $educatorToRegister   = $this->educatorUserRepository->find($educatorIdToRegister);


        // We need to delete any previous "registration" for the educator. This fixes the issue of an educator
        // trying to cancel a registration from an older non-educatorRegisterEducatorForCompanyExperienceRequest.

        $registration = $this->registrationRepository->getByUserAndExperience($educatorToRegister, $experience);
        if ($registration) {
            $this->entityManager->remove($registration);
            $this->entityManager->flush();
        }

        // We will mark any educator as approved for this event.
        $user            = $this->getUser();
        $registerRequest = new EducatorRegisterEducatorForCompanyExperienceRequest();
        $registerRequest->setCreatedBy($user);
        $registerRequest->setNeedsApprovalBy($experience->getEmployeeContact());
        $registerRequest->setCompanyExperience($experience);
        $registerRequest->setEducatorUser($educatorToRegister);

        // Does not require approval
        $registerRequest->setApproved(true);
        $registerRequest->setProfessionalHasSeen(true);
        $registerRequest->setEducatorHasSeen(true);
        $this->entityManager->persist($registerRequest);
        $this->entityManager->flush();
        $this->addFlash('success', 'You has been registered.');

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            return new JsonResponse(
                [
                    "status" => "success",
                    "educator_id" => $educatorToRegister->getId(),
                    'id' => $experience->getId(),
                    "approval" => $experience->getRequireApproval(),
                    "request_id" => $registerRequest->getId(),
                ]
            );
        } else {
            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }
    }


    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/companies/experiences/{id}/educator/deregister", name="company_experience_educator_deregister", options = { "expose" = true }, methods={"POST"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function companyExperienceEducatorDeregisterAction(Request $request, CompanyExperience $experience)
    {
        $educatorIdToDeregister = $request->request->get('educatorId');
        $educatorToDeregister   = $this->educatorUserRepository->find($educatorIdToDeregister);

        $deregisterEducatorForExperience = $this->educatorRegisterEducatorForCompanyExperienceRequestRepository->getByEducatorAndExperience($educatorToDeregister, $experience);

        $deregisterRequest = $this->requestRepository->find($deregisterEducatorForExperience);
        $registration      = $this->registrationRepository->getByUserAndExperience($educatorToDeregister, $experience);

        /** @var ProfessionalUser $companyOwner */
        $companyOwner = $experience->getCompany()->getOwner();

        $this->entityManager->remove($deregisterEducatorForExperience);
        $this->entityManager->remove($deregisterRequest);
        if ($registration) {
            $this->entityManager->remove($registration);
        }
        $this->entityManager->persist($experience);
        $this->entityManager->flush();
        // 

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            return new JsonResponse(
                [
                    "status" => "success",
                    "educator_id" => $educatorIdToDeregister,
                    'id' => $experience->getId(),
                ]
            );
        } else {
            $this->addFlash('success', 'You have been removed from this experience.');

            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }
    }


    /**
     * @IsGranted("ROLE_SCHOOL_ADMINISTRATOR_USER")
     * @Route("/companies/experiences/{id}/school_administrator/register", name="company_experience_school_admin_register", options = { "expose" = true }, methods={"POST"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyExperienceSchoolAdministratorRegisterAction(Request $request, CompanyExperience $experience)
    {

        $schoolAdminIdToRegister = $request->request->get('schoolAdminId');
        $schoolAdminToRegister   = $this->schoolAdministratorRepository->find($schoolAdminIdToRegister);


        // We need to delete any previous "registration" for the school admin. This fixes the issue of an school admin
        // trying to cancel a registration from an older non-shooolAdminRegisterSAForCompanyExperienceRequest.

        $registration = $this->registrationRepository->getByUserAndExperience($schoolAdminToRegister, $experience);
        if ($registration) {
            $this->entityManager->remove($registration);
            $this->entityManager->flush();
        }

        // We will mark any educator as approved for this event.
        $user            = $this->getUser();
        $registerRequest = new SchoolAdminRegisterSAForCompanyExperienceRequest();
        $registerRequest->setCreatedBy($user);
        $registerRequest->setNeedsApprovalBy($experience->getEmployeeContact());
        $registerRequest->setCompanyExperience($experience);
        $registerRequest->setSchoolAdminUser($schoolAdminToRegister);

        // Does not require approval
        $registerRequest->setApproved(true);
        $registerRequest->setProfessionalHasSeen(true);
        $registerRequest->setSchoolAdminHasSeen(true);
        $this->entityManager->persist($registerRequest);
        $this->entityManager->flush();
        $this->addFlash('success', 'You has been registered.');

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            return new JsonResponse(
                [
                    "status" => "success",
                    "school_admin_id" => $schoolAdminToRegister->getId(),
                    'id' => $experience->getId(),
                    "approval" => $experience->getRequireApproval(),
                    "request_id" => $registerRequest->getId(),
                ]
            );
        } else {
            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }
    }


    /**
     * @IsGranted("ROLE_SCHOOL_ADMINISTRATOR_USER")
     * @Route("/companies/experiences/{id}/school_administrator/deregister", name="company_experience_school_admin_deregister", options = { "expose" = true }, methods={"POST"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function companyExperienceSchoolAdministratorDeregisterAction(Request $request, CompanyExperience $experience
    ) {
        $schoolAdminIdToDeregister = $request->request->get('schoolAdminId');
        $schoolAdminToDeregister   = $this->schoolAdministratorRepository->find($schoolAdminIdToDeregister);

        $deregisterSchoolAdminForExperience = $this->schoolAdminRegisterSAForCompanyExperienceRequestRepository->getBySchoolAdministratorAndExperience($schoolAdminToDeregister, $experience);

        $deregisterRequest = $this->requestRepository->find($deregisterSchoolAdminForExperience);
        $registration      = $this->registrationRepository->getByUserAndExperience($schoolAdminToDeregister, $experience);

        /** @var ProfessionalUser $companyOwner */
        $companyOwner = $experience->getCompany()->getOwner();

        $this->entityManager->remove($deregisterSchoolAdminForExperience);
        $this->entityManager->remove($deregisterRequest);
        if ($registration) {
            $this->entityManager->remove($registration);
        }
        $this->entityManager->persist($experience);
        $this->entityManager->flush();
        // 

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            return new JsonResponse(
                [
                    "status" => "success",
                    "school_admin_id" => $schoolAdminIdToDeregister,
                    'id' => $experience->getId(),
                ]
            );
        } else {
            $this->addFlash('success', 'You have been removed from this experience.');

            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }
    }


    /**
     * @IsGranted({"ROLE_EDUCATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/companies/experiences/{id}/students/register", name="company_experience_student_register", options = { "expose" = true }, methods={"POST"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyExperienceStudentRegisterAction(Request $request, CompanyExperience $experience)
    {
        // We need to check if the experience requires approval from the event creator. If so follow the
        // current flow, otherwise, bypass sending emails and mark the registration as complete.
        $studentIdToRegister = $request->request->get('studentId');
        $studentToRegister   = $this->studentUserRepository->find($studentIdToRegister);

        if ($experience->getAvailableSpaces() === 0) {
            $this->addFlash('error', sprintf('Could not register students. 0 spots left.'));

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(
                    [
                        "status" => "failure",
                        "message" => 'Could not register students. 0 spots left.',
                        "student_id" => $studentIdToRegister,
                        'id' => $experience->getId(),
                    ]
                );
            } else {
                return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
            }

        }
        /** @var User $user */
        $user            = $this->getUser();
        $registerRequest = new EducatorRegisterStudentForCompanyExperienceRequest();
        $registerRequest->setCreatedBy($user);
        $registerRequest->setNeedsApprovalBy($experience->getEmployeeContact());
        $registerRequest->setCompanyExperience($experience);
        $registerRequest->setStudentUser($studentToRegister);

        if ($experience->getRequireApproval()) {
            // Requires approval
            $this->entityManager->persist($registerRequest);
            $this->entityManager->flush();
            $this->requestsMailer->educatorRegisterStudentForCompanyExperienceRequest($registerRequest);
            $this->addFlash('success', 'Registration request successfully sent.');
        } else {
            // Does not require approval
            $registerRequest->setApproved(true);
            $registerRequest->setProfessionalHasSeen(true);

            if ($user->isEducator()) {
                $registerRequest->setEducatorHasSeen(true);
            }
            if ($user->isSchoolAdministrator()) {
                $registerRequest->setSchoolAdminHasSeen(true);
            }

            $this->entityManager->persist($registerRequest);
            $this->entityManager->flush();
            $this->addFlash('success', 'Student has been registered.');
        }

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            return new JsonResponse(
                [
                    "status" => "success",
                    "student_id" => $studentIdToRegister,
                    'id' => $experience->getId(),
                    "approval" => $experience->getRequireApproval(),
                    "request_id" => $registerRequest->getId(),
                ]
            );
        } else {
            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }
    }

    /**
     * @IsGranted({"ROLE_EDUCATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/companies/experiences/{id}/students/deregister", name="company_experience_student_deregister", options = { "expose" = true }, methods={"POST"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function companyExperienceStudentDeregisterAction(Request $request, CompanyExperience $experience)
    {
        $studentIdToDeregister = $request->request->get('studentId');
        $studentToDeregister   = $this->studentUserRepository->find($studentIdToDeregister);

        $deregisterStudentForExperience = $this->educatorRegisterStudentForExperienceRequestRepository->getByStudentAndExperience($studentToDeregister, $experience);

        $deregisterRequest = $this->requestRepository->find($deregisterStudentForExperience);

        if ($deregisterRequest->getApproved()) {
            $experience->setAvailableSpaces($experience->getAvailableSpaces() + 1);
        }

        $registration = $this->registrationRepository->getByUserAndExperience($studentToDeregister, $experience);

        /** @var ProfessionalUser $companyOwner */
        $companyOwner = $experience->getCompany()->getOwner();

        if ($companyOwner->getEmail()) {
            $this->requestsMailer->userDeregisterFromEvent($studentToDeregister, $companyOwner, $experience);
        }

        $educators = $studentToDeregister->getEducatorUsers();

        foreach ($educators as $educator) {
            if ($educator->getEmail()) {
                $this->requestsMailer->userDeregisterFromEvent($studentToDeregister, $educator, $experience);
            }
        }

        if ($studentToDeregister->getEmail()) {
            $this->requestsMailer->userDeregisterFromEvent($studentToDeregister, $studentToDeregister, $experience);
        }

        $this->entityManager->remove($deregisterStudentForExperience);
        $this->entityManager->remove($deregisterRequest);
        if ($registration) {
            $this->entityManager->remove($registration);
        }
        $this->entityManager->persist($experience);
        $this->entityManager->flush();
        $this->addFlash('success', 'Student has been removed from this experience.');

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            return new JsonResponse(
                [
                    "status" => "success",
                    "student_id" => $studentIdToDeregister,
                    'id' => $experience->getId(),
                ]
            );
        } else {
            return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
        }
    }

    /**
     * @Route("/companies/experiences/{id}/remove", name="company_experience_remove", options = { "expose" = true })
     * @param Request           $request
     * @param CompanyExperience $experience
     * @param LoggerInterface   $logger
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceRemoveAction(Request $request, CompanyExperience $experience)
    {

        $company = $experience->getCompany();
        $this->denyAccessUnlessGranted('edit', $experience->getCompany());

        $message = $request->query->get('cancellationMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {
            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $message);
        }

        $experience->setCancelled(true);
        $this->entityManager->persist($experience);

        foreach ($experience->getRegistrations() as $registration) {
            $this->entityManager->remove($registration);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Experience successfully removed!');

        return $this->redirectToRoute('company_view', ['id' => $company->getId()]);
    }

    /**
     * @Route("/companies/experiences/{id}/file/add", name="company_experience_file_add", options = { "expose" = true })
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return JsonResponse
     */
    public function experienceAddFileAction(Request $request, CompanyExperience $experience)
    {

        $this->denyAccessUnlessGranted('edit', $experience->getCompany());

        /** @var UploadedFile $resource */
        $resource      = $request->files->get('resource');
        $linkToWebsite = $request->request->get("linkToWebsite");
        $title         = $request->request->get('title');
        $description   = $request->request->get('description');

        // Validate linkToWebsite variable
        if ($linkToWebsite == "http://") {
            $linkToWebsite = null;
        }

        if ($resource && $title) {
            $mimeType    = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::EXPERIENCE_FILE);
            $file        = new ExperienceFile();
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
            $file->setExperience($experience);
            $file->setDescription($description ? $description : null);
            $file->setTitle($title);
            $this->entityManager->persist($file);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::EXPERIENCE_FILE . '/' . $newFilename,
                    'id' => $file->getId(),
                    'title' => $title,
                    'description' => $description,

                ], Response::HTTP_OK
            );
        } else {
            if ($linkToWebsite && $title) {
                $mimeType    = '';
                $newFilename = '';
                $file        = new ExperienceFile();
                $file->setOriginalName($newFilename);
                $file->setMimeType($mimeType);
                $file->setFileName($newFilename);
                $file->setFile(null);
                $file->setExperience($experience);
                $file->setDescription($description ? $description : null);
                $file->setLinkToWebsite($linkToWebsite);
                $file->setTitle($title);
                $this->entityManager->persist($file);
                $this->entityManager->flush();

                return new JsonResponse(
                    [
                        'success' => true,
                        'url' => $linkToWebsite,
                        'id' => $file->getId(),
                        'title' => $title,
                        'description' => $description,

                    ], Response::HTTP_OK
                );
            }
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_BAD_REQUEST
        );
    }


    /**
     * @Route("/companies/experiences/file/{id}/get", name="company_experience_file_get", options = { "expose" = true })
     * @param Request        $request
     * @param ExperienceFile $file
     *
     * @return JsonResponse
     */
    public function experienceGetFileAction(Request $request, ExperienceFile $file)
    {
        $this->denyAccessUnlessGranted('edit', $file->getExperience()->getCompany());


        if ($file->getFile() != null) {
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::EXPERIENCE_FILE . '/' . $file->getFileName(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success' => true,
                    'website' => $file->getLinkToWebsite(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        }
    }

    /**
     * @Route("/companies/experiences/file/{id}/edit", name="company_experience_file_edit", options = { "expose" = true })
     * @param Request        $request
     * @param ExperienceFile $file
     *
     * @return JsonResponse
     */
    public function experienceEditFileAction(Request $request, ExperienceFile $file)
    {

        $this->denyAccessUnlessGranted('edit', $file->getExperience()->getCompany());

        /** @var UploadedFile $resource */
        $resource      = $request->files->get('resource');
        $title         = $request->request->get('title');
        $description   = $request->request->get('description');
        $linkToWebsite = $request->request->get('linkToWebsite');

        if ($title) {
            $file->setTitle($title);
        }

        if ($description) {
            $file->setDescription($description);
        }

        if ($linkToWebsite && $linkToWebsite != "http://") {
            $file->setLinkToWebsite($linkToWebsite);
        } else {
            $file->setLinkToWebsite(null);
        }

        if ($resource) {
            $mimeType    = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::EXPERIENCE_FILE);
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
        } else {
            $file->setOriginalName(null);
            $file->setMimeType(null);
            $file->setFileName(null);
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();


        if ($file->getFileName() != null) {
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::EXPERIENCE_FILE . '/' . $file->getFileName(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $file->getLinkToWebsite(),
                    'id' => $file->getId(),
                    'title' => $file->getTitle(),
                    'description' => $file->getDescription(),

                ], Response::HTTP_OK
            );
        }
    }

    /**
     * @Route("/companies/experiences/files/{id}/remove", name="company_experience_file_remove", options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "company_id"})
     * @ParamConverter("companyResource", options={"id" = "resource_id"})
     * @param Request        $request
     * @param ExperienceFile $experienceFile
     *
     * @return JsonResponse
     */
    public function experienceRemoveFileAction(Request $request, ExperienceFile $experienceFile)
    {

        $this->denyAccessUnlessGranted('edit', $experienceFile->getExperience()->getCompany());

        $this->entityManager->remove($experienceFile);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @IsGranted("ROLE_EDUCATOR_USER")
     * @Route("/companies/experiences/{id}/students/forward", name="company_experience_bulk_notify", options = { "expose" = true }, methods={"POST"})
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \ReflectionException
     */
    public function companyExperienceBulkNotifyAction(Request $request, CompanyExperience $experience)
    {
        $message = $request->get('message', '');

        $message = sprintf("Experience: %s Message: %s", $experience->getTitle(), $message);

        $students = $request->get('students');

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        foreach ($students as $student) {

            /** @var StudentUser $student */
            $student = $this->studentUserRepository->find($student);
            $this->experienceMailer->experienceForward($experience, $student, $message, $loggedInUser);


            $chat = $this->chatRepository->findOneBy(
                [
                    'userOne' => $loggedInUser,
                    'userTwo' => $student,
                ]
            );

            if (!$chat) {
                $chat = $this->chatRepository->findOneBy(
                    [
                        'userOne' => $student,
                        'userTwo' => $loggedInUser,
                    ]
                );
            }

            // if a chat doesn't exist then let's create one!
            if (!$chat) {
                $chat = new Chat();
                $chat->setUserOne($student);
                $chat->setUserTwo($loggedInUser);
                $this->entityManager->persist($chat);
                $this->entityManager->flush();
            }


            $notice = $message;

            $chatMessage = new ChatMessage();
            $chatMessage->setBody($notice);
            $chatMessage->setSentFrom($loggedInUser);
            $chatMessage->setSentAt(new \DateTime());
            $chatMessage->setChat($chat);

            // Figure out which user to message from the chat object
            $userToMessage = $chat->getUserOne()->getId() === $loggedInUser->getId() ? $chat->getUserTwo() : $chat->getUserOne();
            $chatMessage->setSentTo($userToMessage);

            $this->entityManager->persist($chatMessage);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Experience has been sent to students!');

        return $this->redirectToRoute('company_experience_view', ['id' => $experience->getId()]);
    }


    /**
     * @Route("/companies/manage", name="manage_companies", options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAction(Request $request)
    {

        /** @var User $user */
        $user      = $this->getUser();
        $companies = $this->companyRepository->getAllCompaniesByName();

        if ($user->isRegionalCoordinator()) {

            /** @var RegionalCoordinator $user */

            $companies = array_filter(
                $companies, function (Company $company) use ($user) {

                $companyRegions = [];
                foreach ($company->getRegions() as $region) {
                    $companyRegions[] = $region->getId();
                }

                if (in_array($user->getRegion()->getId(), $companyRegions)) {
                    return true;
                }

                return false;
            }
            );
        }

        $companies = array_values($companies);

        return $this->render(
            'company/manage.html.twig', [
                'companies' => $companies,
                'user' => $user,
            ]
        );
    }

    /**
     * @Route("/companies/{id}/toggle-status", name="toggle_company_status", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleCompanyStatus(Request $request, Company $company)
    {

        $company->setApproved($request->request->get('newStatus'));

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        if ($request->request->get('newStatus') == 1) {

            // todo make sure in the RequestController we are sending this email as well
            $this->requestsMailer->newCompanyApproved($company);

            $button = '<button class="uk-button uk-button-small uk-label-success" data-id="' . $company->getId() . '" data-newstatus="0">Approved</button>';
        } else {
            $button = '<button class="uk-button uk-button-small uk-label-warning" data-id="' . $company->getId() . '" data-newstatus="1">Denied</button>';
            $button .= '<button class="uk-button uk-button-small uk-label-danger" data-id="' . $company->getId() . '">Delete</button>';
        }


        $html = "<td><a href='/companies/" . $company->getId() . "/edit'>" . $company->getName() . "</a></td>";
        $html .= "<td>" . $company->getOwner()->getFullName() . "</td>";
        $html .= "<td>" . $company->getStreet() . "<br />" . $company->getCity() . ", " . ($company->getState() ? $company->getState()->getAbbreviation() : "") . " " . $company->getZipCode() . "</td>";
        $html .= "<td>" . $button . "</td>";

        return new JsonResponse(["status" => "success", "html" => $html]);
    }


    /**
     * @Route("/companies/{id}/page-visits", name="company_page_visits", options = { "expose" = true })
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pageVisitsAction(Request $request, Company $company)
    {
        $visits_7   = $this->getDoctrine()->getRepository(CompanyView::class)->getVisits(7, $company->getId());
        $visits_30  = $this->getDoctrine()->getRepository(CompanyView::class)->getVisits(30, $company->getId());
        $visits_90  = $this->getDoctrine()->getRepository(CompanyView::class)->getVisits(90, $company->getId());
        $visits_365 = $this->getDoctrine()->getRepository(CompanyView::class)->getVisits(365, $company->getId());
        $user       = $this->getUser();

        return $this->render(
            'company/page_visits.html.twig', [
                'company' => $company,
                'user' => $user,
                'visits_7' => $visits_7,
                'visits_30' => $visits_30,
                'visits_90' => $visits_90,
                'visits_365' => $visits_365,
            ]
        );
    }

    /**
     * @Route("/companies/experiences/{id}/toggle-feedback-view", name="toggle_company_feedback_view", options = { "expose" = true })
     * @param Request           $request
     * @param CompanyExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleCanViewFeedback(Request $request, CompanyExperience $experience)
    {

        $experience->setCanViewFeedback($request->request->get('val'));
        $this->entityManager->persist($experience);
        $this->entityManager->flush();

        return new JsonResponse(["status" => "success", "canView" => $request->request->get('val')]);
    }

    /**
     * List all errors of a given bound form.
     *
     * @param Form $form
     *
     * @return array
     */
    protected function getFormErrors(Form $form)
    {
        $errors = array ();

        // Global
        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        // Fields
        foreach ($form as $child/** @var Form $child */) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }
}
