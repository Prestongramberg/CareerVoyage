<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Entity\Company;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
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
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ProfileController
 * @package App\Controller
 * @Route("/dashboard")
 */
class ProfileController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/profiles/{id}/view", name="profile_index", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @param User $profileUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, User $profileUser) {

        $user = $this->getUser();
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'profileUser' => $profileUser
        ]);
    }

    /**
     * @Route("/test", name="profile_test", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction(Request $request) {

        $user = $this->getUser();
        return $this->render('profile/test.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/profiles/{id}/edit", name="profile_edit")
     * @param Request $request
     * @param User $user
     * @return JsonResponse|Response
     */
    public function editAction(Request $request, User $user) {

        $this->denyAccessUnlessGranted('edit', $user);

        $options = [
            'method' => 'POST',
        ];

        if($user->isAdmin()) {
            $form = $this->createForm(AdminProfileFormType::class, $user, $options);
            /** @var AdminUser $user */
        } elseif (($user->isSiteAdmin())) {
            $form = $this->createForm(SiteAdminProfileFormType::class, $user, $options);
            /** @var ProfessionalUser $user */
        } elseif (($user->isProfessional())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(ProfessionalEditProfileFormType::class, $user, $options);
            /** @var ProfessionalUser $user */
        } elseif (($user->isSchoolAdministrator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(SchoolAdministratorEditProfileFormType::class, $user, $options);
            /** @var SchoolAdministrator $user */
        } elseif (($user->isEducator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(EducatorEditProfileFormType::class, $user, $options);
            /** @var EducatorUser $user */
        } elseif (($user->isStudent())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(StudentEditProfileFormType::class, $user, $options);
            /** @var StudentUser $user */
        } elseif (($user->isStateCoordinator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(StateCoordinatorEditProfileFormType::class, $user, $options);
            /** @var StateCoordinator $user */
        } elseif (($user->isRegionalCoordinator())) {
            $options['skip_validation'] = $request->request->get('skip_validation', false);
            $form = $this->createForm(RegionalCoordinatorEditProfileFormType::class, $user, $options);
            /** @var RegionalCoordinator $user */
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            if($user->getPlainPassword()) {
                $encodedPassword = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encodedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile successfully updated');
            return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
        }

        if($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView('api/form/secondary_industry_form_field.html.twig', [
                        'form' => $form->createView()
                    ])
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $this->getDoctrine()->getManager()->refresh($user);

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profiles/{id}/photo/add", name="profile_photo_add", options = { "expose" = true })
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function profileAddPhotoAction(Request $request, User $user) {

        $this->denyAccessUnlessGranted('edit', $user);

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $profilePhoto = $request->files->get('file');

        if($profilePhoto) {
            $newFilename = $this->uploaderHelper->upload($profilePhoto);
            $user->setPhoto($newFilename);
            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::PROFILE_PHOTO) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::PROFILE_PHOTO.'/'.$newFilename, 'squared_thumbnail_small')
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
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, User $user) {

        $this->denyAccessUnlessGranted('edit', $user);

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirectToRoute('welcome');
    }

    /**
     * @Route("/profile/{id}/deactivate", name="profile_deactivate")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deactivateAction(Request $request, User $user) {

        $this->denyAccessUnlessGranted('edit', $user);

        $user->setActivated(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
    }

    /**
     * @Route("/profile/{id}/reactivate", name="profile_reactivate")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reactivateAction(Request $request, User $user) {

        $this->denyAccessUnlessGranted('edit', $user);

        $user->setActivated(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
    }

}