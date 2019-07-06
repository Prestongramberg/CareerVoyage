<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Service\FileUploader;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ProfileController
 * @package App\Controller
 */
class ProfileController extends AbstractController
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
     * ProfileController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * @Route("/profile/{id}/view", name="profile_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, User $user) {

        return $this->render('profile/index.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/profile/{id}/edit", name="profile_edit")
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, ProfessionalUser $professionalUser) {

        $form = $this->createForm(ProfessionalEditProfileFormType::class, $professionalUser, [
            'method' => 'POST',
        ]);

        $originalPassword = $professionalUser->getPassword();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var ProfessionalUser $professionalUser */
            $professionalUser = $form->getData();

            // if there is no password just set the password back to the original
            if(!$professionalUser->getPassword()) {
                $professionalUser->setPassword($originalPassword);
            } else {
                $encodedPassword = $this->passwordEncoder->encodePassword($professionalUser, $professionalUser->getPassword());
                $professionalUser->setPassword($encodedPassword);
            }

            $uploadFile = $form->get('file')->getData();

            if($uploadFile) {

                $newFileName = $this->newFileName($uploadFile);
                $path = $this->fileUploader->uploadPhoto($uploadFile, $newFileName);

                $image = new Image();
                $image->setOriginalName($this->getOriginalName($uploadFile));
                $image->setMimeType($uploadFile->getMimeType());
                $image->setNewName($newFileName);
                $image->setPath($path);

                $professionalUser->setPhoto($image);
            }

            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        $deleteForm = $this->createForm(ProfessionalDeleteProfileFormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_delete', ['id' => $professionalUser->getId()])
        ]);

        $deactivateForm = $this->createForm(ProfessionalDeactivateProfileFormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_deactivate', ['id' => $professionalUser->getId()])
        ]);

        $reactivateForm = $this->createForm(ProfessionalReactivateProfileFormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_reactivate', ['id' => $professionalUser->getId()])
        ]);

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $professionalUser,
            'deleteForm' => $deleteForm->createView(),
            'deactivateForm' => $deactivateForm->createView(),
            'reactivateForm' => $reactivateForm->createView(),

        ]);
    }

    /**
     * @Route("/profile/{id}/delete", name="profile_delete")
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, ProfessionalUser $professionalUser) {

        $form = $this->createForm(ProfessionalDeleteProfileFormType::class, $professionalUser, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $professionalUser->setDeleted(true);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('profile_edit', ['id' => $professionalUser->getId()]);
    }

    /**
     * @Route("/profile/{id}/deactivate", name="profile_deactivate")
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deactivateAction(Request $request, ProfessionalUser $professionalUser) {

        $form = $this->createForm(ProfessionalDeactivateProfileFormType::class, $professionalUser, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $professionalUser->setDeactivated(true);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('profile_edit', ['id' => $professionalUser->getId()]);
    }

    /**
     * @Route("/profile/{id}/reactivate", name="profile_reactivate")
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reactivateAction(Request $request, ProfessionalUser $professionalUser) {

        $form = $this->createForm(ProfessionalReactivateProfileFormType::class, $professionalUser, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $professionalUser->setDeactivated(false);
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('profile_edit', ['id' => $professionalUser->getId()]);
    }

}