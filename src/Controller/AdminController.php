<?php

namespace App\Controller;

use App\Entity\EmailLog;
use App\Entity\RolesWillingToFulfill;
use App\Entity\User;
use App\Form\EventTypeFormType;
use App\Form\RolesFormType;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class AdminController
 *
 * @package App\Controller
 * @Route("/dashboard/admin")
 */
class AdminController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/roles/new", name="admin_role_new")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newRole(Request $request)
    {

        $user = $this->getUser();
        $role = new RolesWillingToFulfill();

        $form = $this->createForm(
            RolesFormType::class, $role, [
                                    'method' => 'POST',
                                ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $role RolesWillingToFulfill */
            $role = $form->getData();
            $role->setInRoleDropdown(true);
            $role->setEventName($role->getName());

            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $this->addFlash('success', 'New role has been created.');

            return $this->redirectToRoute('admin_role_new');
        }

        return $this->render(
            'admin/new_role.html.twig', [
                                          'user' => $user,
                                          'form' => $form->createView(),
                                      ]
        );
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER"})
     * @Route("/email-log/download", name="admin_email_log_download")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function emailLogDownload(Request $request, SerializerInterface $serializer)
    {

        /** @var User $user */
        $user = $this->getUser();

        $emailLogs = $this->entityManager->getRepository(EmailLog::class)->findAll();

        $logs[] =
            [
                'from',
                'to',
                'subject',
                'status',
                'date',
            ];

        foreach ($emailLogs as $emailLog) {
            $logs[] = [
                $emailLog->getFromEmail(),
                $emailLog->getToEmail(),
                $emailLog->getSubject(),
                $emailLog->getStatus(),
                $emailLog->getCreatedAt()->format('m/d/Y h:i:s A'),
            ];
        }

        $response = new Response(
            $serializer->encode(
                $logs, 'csv', [
                         \Symfony\Component\Serializer\Encoder\CsvEncoder::NO_HEADERS_KEY => true,
                     ]
            )
        );

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=email_log.csv");

        return $response;

    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER"})
     * @Route("/email-log/users/{id}/view", name="admin_email_log_view")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function viewEmails(Request $request, User $user)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $emailLogs = [];
        if ($user->getEmail()) {
            $emailLogs = $this->entityManager->getRepository(EmailLog::class)->findBy(
                [
                    'toEmail' => $user->getEmail(),
                ]
            );
        }

        return $this->render(
            'admin/email_logs.html.twig', [
                                            'user'         => $user,
                                            'loggedInUser' => $loggedInUser,
                                            'emailLogs'    => $emailLogs,
                                        ]
        );
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER"})
     * @Route("/email-log/{id}/view", name="admin_email_log_view_singular")
     * @param Request  $request
     *
     * @param EmailLog $emailLog
     *
     * @return string
     */
    public function viewSingularEmail(Request $request, EmailLog $emailLog)
    {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        return new Response($emailLog->getBody());
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/event-types/list", name="admin_event_types_list")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function listEventType(Request $request)
    {
        $user = $this->getUser();

        $roles = $this->rolesWillingToFulfillRepository->findBy(array (), array ('name' => 'ASC'));
        // $roles = $this->rolesWillingToFulfillRepository->findBy(
        //         ['userRole' => $v[0]],
        //         ['position' => 'ASC']
        //     );
        // }

        return $this->render(
            'admin/list_event_type.html.twig', [
                                                 'user'  => $user,
                                                 'roles' => $roles,
                                             ]
        );
    }


    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/event-types/new", name="admin_event_types_new")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newEventType(Request $request)
    {

        $user = $this->getUser();
        $role = new RolesWillingToFulfill();

        $form = $this->createForm(
            EventTypeFormType::class, $role, [
                                        'method' => 'POST',
                                    ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $role RolesWillingToFulfill */
            $role = $form->getData();
            $role->setEventName($role->getName());

            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $this->addFlash('success', 'New experience type has been created.');

            return $this->redirectToRoute('admin_event_types_list');
        }

        return $this->render(
            'admin/new_event_type.html.twig', [
                                                'user' => $user,
                                                'form' => $form->createView(),
                                            ]
        );
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/event-types/{id}/edit", name="admin_event_types_edit", options = { "expose" = true })
     * @param Request               $request
     * @param RolesWillingToFulfill $role
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function editEventType(Request $request, RolesWillingToFulfill $role)
    {

        $user = $this->getUser();


        $form = $this->createForm(
            EventTypeFormType::class, $role, [
                                        'method' => 'POST',
                                    ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $role RolesWillingToFulfill */
            $role = $form->getData();
            $role->setEventName($role->getName());

            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $this->addFlash('success', 'Experience type has been edited.');

            return $this->redirectToRoute('admin_event_types_list');
        }

        return $this->render(
            'admin/edit_event_type.html.twig', [
                                                 'user' => $user,
                                                 'form' => $form->createView(),
                                             ]
        );
    }
}