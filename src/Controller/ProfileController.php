<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Entity\Company;
use App\Entity\EducatorUser;
use App\Entity\Feedback;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\AdminProfileFormType;
use App\Form\EducatorEditProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RegionalCoordinatorEditProfileFormType;
use App\Form\SchoolAdministratorEditProfileFormType;
use App\Form\SiteAdminProfileFormType;
use App\Form\StateCoordinatorEditProfileFormType;
use App\Form\StudentEditProfileFormType;
use App\Repository\RegionalCoordinatorRepository;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;


/**
 * Class ProfileController
 *
 * @package App\Controller
 * @Route("/dashboard")
 */
class ProfileController extends AbstractController
{

    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/profiles/{id}/view", name="profile_index", methods={"GET"}, options = { "expose" = true })
     * @param  Request  $request
     * @param  User     $profileUser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, User $profileUser)
    {
        $user       = $this->getUser();
        $dashboards = [];

        if ($profileUser->isStudent()) {
            /** @var StudentUser $profileUser */
            $lessonFavorites                    = $this->lessonFavoriteRepository->findBy(['user' => $profileUser], ['createdAt' => 'DESC']);
            $companyFavorites                   = $this->companyFavoriteRepository->findBy(['user' => $profileUser], ['createdAt' => 'DESC']);
            $upcomingEventsRegisteredForByUser  = $this->experienceRepository->getUpcomingEventsRegisteredForByUser($profileUser);
            $completedEventsRegisteredForByUser = $this->experienceRepository->getCompletedEventsRegisteredForByUser($profileUser);
            $primaryIndustries                  = $this->industryRepository->findAll();

            $guestLectures = $this->teachLessonExperienceRepository->findBy([
                'school' => $profileUser->getSchool(),
            ]);

            $dashboards = [
                'companyFavorites'                   => $companyFavorites,
                'lessonFavorites'                    => $lessonFavorites,
                'upcomingEventsRegisteredForByUser'  => $upcomingEventsRegisteredForByUser,
                'completedEventsRegisteredForByUser' => $completedEventsRegisteredForByUser,
                'guestLectures'                      => $guestLectures,
                'eventsWithFeedback'                 => [],
                'eventsMissingFeedback'              => [],
                'eventsWithFeedbackFromOthers'       => [],
                'primaryIndustries'                  => $primaryIndustries,
            ];

            // let's see which events have feedback from the user and which don't
            foreach ($completedEventsRegisteredForByUser as $event) {
                $allFeedback = $this->feedbackRepository->findBy([
                    'experience' => $event,
                ]);

                /** @var Feedback $feedback */
                foreach ($allFeedback as $feedback) {
                    if ($feedback->getUser()
                                 ->getId() !== $profileUser->getId()
                    ) {
                        $dashboards['eventsWithFeedbackFromOthers'][] = $event;
                        break;
                    }
                }


                $feedback = $this->feedbackRepository->findOneBy([
                    'user'       => $profileUser,
                    'experience' => $event,
                ]);

                if (!$feedback) {
                    $dashboards['eventsMissingFeedback'][] = [
                        'event'    => $event,
                        'feedback' => $feedback,
                    ];
                } else {
                    $dashboards['eventsWithFeedback'][] = [
                        'event'    => $event,
                        'feedback' => $feedback,
                    ];
                }
            }
        }

        return $this->render('profile/index.html.twig', [
            'user'        => $user,
            'profileUser' => $profileUser,
            'dashboards'  => $dashboards,
        ]);
    }

    /**
     * @Route("/test", name="profile_test", methods={"GET"})
     * @param  Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction(Request $request)
    {
        $user = $this->getUser();

        return $this->render('profile/test.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profiles/{id}/edit", name="profile_edit", options = { "expose" = true })
     * @param  Request  $request
     * @param  User     $user
     *
     * @return JsonResponse|Response
     */
    public function editAction(Request $request, User $user)
    {
        $loggedInUser = $this->getUser();
        $this->denyAccessUnlessGranted('edit', $user);
        $tab = $request->request->get('tab', '');

        $editVideoId       = $request->query->get('videoEdit', null);
        $professionalVideo = null;
        if ($editVideoId) {
            $professionalVideo = $this->videoRepository->find($editVideoId);
        }

        $options = [
            'method' => 'POST',
        ];

        $countyJson = [];
        $counties   = $this->countyRepository->findAll();
        foreach ($counties as $county) {
            $countyJson[] = [
                'coordinates'              => $county->getCoordinates(),
                'name'                     => $county->getName(),
                'state_name'               => $county->getStateName(),
                'region_name'              => $county->getRegionName(),
                'service_cooperative_name' => $county->getServiceCooperativeName(),
                'color'                    => $county->getColor() ?? '#FF0000',
            ];
        }

        if ($user->isAdmin()) {
            $options['user'] = $user;
            $form            = $this->createForm(AdminProfileFormType::class, $user, $options);
            /** @var AdminUser $user */
        } elseif (($user->isSiteAdmin())) {
            $options['user'] = $user;
            $form            = $this->createForm(SiteAdminProfileFormType::class, $user, $options);
            /** @var ProfessionalUser $user */
        } elseif (($user->isProfessional())) {
            $options = [
                'method'          => 'POST',
                'user'            => $user,
                'skip_validation' => $request->request->get('skip_validation', false),
            ];

            if (!$options['skip_validation']) {
                $options['validation_groups'] = $request->request->get('validation_groups', []);
            }

            $form = $this->createForm(ProfessionalEditProfileFormType::class, $user, $options);
            /** @var ProfessionalUser $user */
        } elseif (($user->isSchoolAdministrator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $options['user']            = $user;
            $form                       = $this->createForm(SchoolAdministratorEditProfileFormType::class, $user, $options);
            /** @var SchoolAdministrator $user */
        } elseif (($user->isEducator())) {
            /** @var EducatorUser $user */
            foreach ($user->getSecondaryIndustries() as $secondaryIndustry) {
                $user->addPrimaryIndustry($secondaryIndustry->getPrimaryIndustry());
            }

            $options['skip_validation']   = $request->request->get('skip_validation', false);
            $options['educator']          = $user;
            $options['validation_groups'] = $request->request->get('validation_groups', []);
            $form                         = $this->createForm(EducatorEditProfileFormType::class, $user, $options);
            /** @var EducatorUser $user */
        } elseif (($user->isStudent())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $options['user']            = $user;
            $form                       = $this->createForm(StudentEditProfileFormType::class, $user, $options);
            /** @var StudentUser $user */
        } elseif (($user->isStateCoordinator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $options['user']            = $user;
            $form                       = $this->createForm(StateCoordinatorEditProfileFormType::class, $user, $options);
            /** @var StateCoordinator $user */
        } elseif (($user->isRegionalCoordinator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $options['user']            = $user;
            $form                       = $this->createForm(RegionalCoordinatorEditProfileFormType::class, $user, $options);
            /** @var RegionalCoordinator $user */
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setProfileCompleted(true);

            if ($user->isProfessional()) {
                /** @var ProfessionalUser $user */
                $shouldAttemptGeocode = $user->getStreet() && $user->getCity() && $user->getState() && $user->getZipcode();
                if ($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($user->getFormattedAddress())) {
                    $user->setLongitude($coordinates['lng']);
                    $user->setLatitude($coordinates['lat']);
                }
            }

            if ($user->getPlainPassword()) {
                $encodedPassword = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encodedPassword);
            }

            if ($user->isSchoolAdministrator()) {
                // Loop through all school ids, if current user does not contain the school remove it, else add it.
                $data = $request->request->all(); // Saves post data as an array

                if (array_key_exists('schools', $data['school_administrator_edit_profile_form'])) {
                    $schools = $this->schoolRepository->findBy(['site' => $user->getSite()]);
                    foreach ($schools as $school) {
                        if (!$school->isUserSchoolAdministrator($user) && in_array($school->getId(), $data['school_administrator_edit_profile_form']['schools'])) {
                            $school->addSchoolAdministrator($user);
                        } else {
                            if ($school->isUserSchoolAdministrator($user) && !in_array($school->getId(), $data['school_administrator_edit_profile_form']['schools'])) {
                                $school->removeSchoolAdministrator($user);
                            }
                        }
                    }
                }
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile successfully updated');

            $isGuestInstructor = $request->request->get('guestInstructor', null);
            if ($isGuestInstructor) {
                return $this->redirectToRoute('lesson_index');
            } else {
                $url = $this->generateUrl('profile_edit', ['id' => $user->getId()]).$tab;

                return $this->redirect($url);
            }
        }

        if ($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success'    => false,
                    'formMarkup' => $this->renderView('profile/edit.html.twig', [
                        'form'              => $form->createView(),
                        'user'              => $user,
                        'professionalVideo' => $professionalVideo,
                        'countyJson'        => $countyJson,
                        'loggedInUser'      => $loggedInUser,
                    ]),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        if ($request->request->has('changeableField')) {
            return new JsonResponse(
                [
                    'success'    => false,
                    'formMarkup' => $this->renderView('profile/edit.html.twig', [
                        'form'              => $form->createView(),
                        'user'              => $user,
                        'professionalVideo' => $professionalVideo,
                        'countyJson'        => $countyJson,
                        'loggedInUser'      => $loggedInUser,
                    ]),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return $this->render('profile/edit.html.twig', [
            'form'              => $form->createView(),
            'user'              => $user,
            'professionalVideo' => $professionalVideo,
            'countyJson'        => $countyJson,
            'loggedInUser'      => $loggedInUser,
        ]);
    }

    /**
     * @Route("/profiles/{id}/activate-deactivate", name="profile_activate_deactivate", options = { "expose" = true })
     * @param  Request  $request
     * @param  User     $user
     *
     * @return JsonResponse|Response
     */
    public function activateDeactivateAction(Request $request, User $user)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $route = $request->query->get('route');

        if (!$loggedInUser->isAdmin() && !$loggedInUser->isSiteAdmin()) {
            throw new AccessDeniedException("You do not have user permissions to activate or deactivate accounts.");
        }

        if ($user->getActivated()) {
            $user->setActivated(false);
            if ($request->isXmlHttpRequest()) {
                $button = '<button class="uk-button uk-button-small uk-label-warning" data-href="/dashboard/profiles/'.$user->getId().'/activate-deactivate" data-id="'.$user->getId().'">Inactive</button>';
                $button .= '<button class="uk-button uk-button-small uk-label-danger" data-href="/dashboard/profile/'.$user->getId().'/delete" data-id="'.$user->getId().'">Delete</button>';
            } else {
                $this->addFlash('success', 'User account deactivated');
            }
        } else {
            $user->setActivated(true);
            if ($request->isXmlHttpRequest()) {
                $button = '<button class="uk-button uk-button-small uk-label-success" data-href="/dashboard/profiles/'.$user->getId().'/activate-deactivate" data-id="'.$user->getId().'">Active</button>';
            } else {
                $this->addFlash('success', 'User account activated');
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            $html = $this->getUserRow($user, $loggedInUser, $button);

            return new JsonResponse(["status" => "success", "html" => $html]);
        } else {
            return $this->redirectToRoute($route);
        }
    }


    /**
     * @Route("/profiles/{id}/reactivate-list", name="profile_list_reactivate", options = { "expose" = true })
     * @param  Request  $request
     * @param  User     $user
     *
     * @return JsonResponse|Response
     */
    public function reactivateListAction(Request $request, User $user)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $route = $request->query->get('route');

        if (!$loggedInUser->isAdmin() && !$loggedInUser->isSiteAdmin()) {
            throw new AccessDeniedException("You do not have user permissions to activate or deactivate accounts.");
        }


        $user->setActivated(true);
        $user->setDeleted(false);
        if ($request->isXmlHttpRequest()) {
            $button = '<button class="uk-button uk-button-small uk-label-success" data-href="/dashboard/profiles/'.$user->getId().'/activate-deactivate" data-id="'.$user->getId().'">Active</button>';
        } else {
            $this->addFlash('success', 'User account activated');
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            // AJAX request
            $html = $this->getUserRow($user, $loggedInUser, $button);

            return new JsonResponse(["status" => "success", "html" => $html]);
        } else {
            return $this->redirectToRoute($route);
        }
    }


    /**
     * @Route("/profiles/{id}/photo/add", name="profile_photo_add", options = { "expose" = true })
     * @param  Request  $request
     * @param  User     $user
     *
     * @return JsonResponse
     */
    public function profileAddPhotoAction(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted('edit', $user);

        /** @var UploadedFile $uploadedFile */
        $profilePhoto = $request->files->get('file');

        if ($profilePhoto) {
            $newFilename = $this->uploaderHelper->upload($profilePhoto);
            $user->setPhoto($newFilename);
            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::PROFILE_PHOTO).'/'.$newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url'     => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::PROFILE_PHOTO.'/'.$newFilename, 'squared_thumbnail_small'),
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
     * @Route("/profile/{id}/delete", name="profile_delete")
     * @param  Request  $request
     * @param  User     $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, User $user)
    {
        //$this->denyAccessUnlessGranted('edit', $user);

        $email = $user->getEmail();

        $email = "DELETED+".$email;

        $user->setEmail($email);
        $user->setDeleted(true);
        $user->setActivated(false);

        if ($user->isProfessional()) {
            $company = $user->getCompany();
            if ($company != null) {
                $company->setOwner(null);
                $this->entityManager->persist($company);

                $user->setCompany(null);
            }
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // if the user being deleted is you then log the user out
        if ($this->getUser()
                 ->getId() === $user->getId()
        ) {
            $this->get('security.token_storage')
                 ->setToken(null);
            $request->getSession()
                    ->invalidate();

            return $this->redirectToRoute('welcome');
        }

        if ($request->isXmlHttpRequest()) {
            $loggedInUser = $this->getUser();
            $button       = '<button class="uk-button uk-button-small uk-label-gray" data-href="/dashboard/profiles/'.$user->getId().'/reactivate-list" data-id="'.$user->getId().'">Deleted</button>';

            $html = $this->getUserRow($user, $loggedInUser, $button);

            return new JsonResponse(["status" => "success", "html" => $html]);
        } else {
            $this->addFlash('success', 'User successfully removed');

            return $this->redirectToRoute('manage_users');
        }
    }

    /**
     * @Route("/profile/{id}/deactivate", name="profile_deactivate")
     * @param  Request  $request
     * @param  User     $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deactivateAction(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted('edit', $user);

        $user->setActivated(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
    }

    /**
     * @Route("/profile/{id}/reactivate", name="profile_reactivate")
     * @param  Request  $request
     * @param  User     $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reactivateAction(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted('edit', $user);

        $user->setActivated(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
    }

    /**
     * @Route("/profiles/mass-delete", name="profiles_mass_delete")
     * @param  Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function massDeleteAction(Request $request, UserRepository $userRepository)
    {
        $user = $this->get('security.token_storage')
                     ->getToken()
                     ->getUser();
        $this->denyAccessUnlessGranted('edit', $user);

        $user_list = $request->request->get('user_id', []);
        $user_role = $request->request->get('user_role', 'manage_users');

        foreach ($user_list as $k => $v) {
            $user = $userRepository->find($v);

            $user->setDeleted(true);
            $user->setActivated(false);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Users successfully removed');

        return $this->redirectToRoute($user_role);
    }

    /**
     * @Route("/profiles/mass-deactivate", name="profiles_mass_deactivate")
     * @param  Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function massDeactivateAction(Request $request, UserRepository $userRepository)
    {
        $user = $this->get('security.token_storage')
                     ->getToken()
                     ->getUser();
        $this->denyAccessUnlessGranted('edit', $user);

        $user_list = $request->request->get('user_id', []);
        $user_role = $request->request->get('user_role', 'manage_users');

        foreach ($user_list as $k => $v) {
            $user = $userRepository->find($v);

            $user->setActivated(false);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Users successfully deactivated');

        return $this->redirectToRoute($user_role);
    }


    private function getUserRow($user, $loggedInUser, $button)
    {
        $login = '<td><a class="uk-button uk-button-small uk-button-default" href="/dashboard?_switch_user='.urlencode($user->getEmail()).'">Login</a></td>';

        $html = '<td><input type="checkbox" class="select-users" value="'.$user->getId().'" /></td>';
        $html .= '<td>'.$user->getId().'</td>';
        $html .= '<td>'.$user->getFirstName().'</td>';
        $html .= '<td>'.$user->getLastName().'</td>';
        $html .= '<td>'.$user->getEmail().'</td>';
        $html .= '<td>'.$user->getUsername().'</td>';

        if ($user->isProfessional()) {
            if ($user->getCompany() != null) {
                $html .= '<td><a href="/dashboard/companies'.$user->getCompany()
                                                                  ->getId().'/edit">'.$user->getCompany()
                                                                                           ->getName().'</a></td>';
            } else {
                $html .= '<td>User does not belong to a company</td>';
            }

            if ($user->getCompany() != null
                && $user->getCompany()
                        ->getOwner()
                        ->getId() == $user->getId()
            ) {
                $html .= '<td>Yes</td>';
            } else {
                $html .= '<td>No</td>';
            }
        }

        if ($user->isEducator() || $user->isStudent()) {
            if ($user->getSchool() != null) {
                $html .= '<td><a href="/dashboard/schools'.$user->getSchool()
                                                                ->getId().'/edit">'.$user->getSchool()
                                                                                         ->getName().'</a></td>';
            } else {
                $html .= '<td></td>';
            }

            if ($user->getSchool() != null
                && $user->getSchool()
                        ->getState() != null
            ) {
                $html .= '<td>'.$user->getSchool()
                                     ->getState()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }

            if ($user->getSite() != null) {
                $html .= '<td>'.$user->getSite()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }

            if ($user->isStudent()) {
                $login = '<td><a class="uk-button uk-button-small uk-button-default" href="/dashboard?_switch_user='.urlencode($user->getUsername()).'">Login</a></td>';
            }
        }


        if ($user->isRegionalCoordinator()) {
            if ($user->getRegion() != null) {
                $html .= '<td>'.$user->getRegion()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }

            if ($user->getRegion() != null
                && $user->getRegion()
                        ->getState() != null
            ) {
                $html .= '<td>'.$user->getRegion()
                                     ->getState()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }

            if ($user->getSite() != null) {
                $html .= '<td>'.$user->getSite()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }
        }

        if ($user->isSchoolAdministrator()) {
            if ($user->getSite() != null) {
                $html .= '<td>'.$user->getSite()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }

            $html .= '<td>';

            $schools = [];
            foreach ($user->getSchools() as $school) {
                $schools[] = '<a href="/dashboard/schools/'.$school->getId().'/edit">'.$school->getName().'</a>';
            }

            $html .= join($schools, '|');
            $html .= '</td>';
            // if($loggedInUser->canLoginAsAnotherUser()) { $html .= $login; }
        }

        if ($user->isStateCoordinator()) {
            if ($user->getSite() != null) {
                $html .= '<td>'.$user->getSite()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }

            if ($user->getState() != null) {
                $html .= '<td>'.$user->getState()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }
            // if($loggedInUser->canLoginAsAnotherUser()) { $html .= $login; }
        }

        if ($user->isSiteAdmin()) {
            if ($user->getSite() != null) {
                $html .= '<td>'.$user->getSite()
                                     ->getName().'</td>';
            } else {
                $html .= '<td></td>';
            }
        }

        if (!in_array(
            'ROLE_PREVIOUS_ADMIN',
            $this->get('security.token_storage')
                 ->getToken()
                 ->getRoles()
        )
        ) {
            if ($user->getActivated() === true) {
                if ($loggedInUser->canLoginAsAnotherUser()) {
                    $html .= $login;
                }
            } else {
                $html .= '<td></td>';
            }
        }


        $html .= '<td><a href="/dashboard/profiles/'.$user->getId().'/edit">Edit</a></td>';
        $html .= "<td>".$button."</td>";

        if ($loggedInUser->isAdmin() || $loggedInUser->isSiteAdmin()) {
            if ($user->isSchoolAdministrator() || $user->isProfessional() || $user->isStudent() || $user->isEducator()) {
                $html .= '<td><a target="_blank" href="{http://localhost:8000/dashboard/reports/email-log/users/'.$user->getId().'/view">Email Logs</a></td>';
                if ($user->getProfileCompleted()) {
                    $html .= '<td>Yes</td>';
                } else {
                    $html .= '<td>No</td>';
                }
            }
        }


        return $html;
    }

}
