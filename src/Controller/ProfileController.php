<?php

namespace App\Controller;

use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\ProfessionalEditProfileFormType;
use App\Service\FileUploader;
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

           /* $uploadFile = $form->get('photo')->getData();

            if($uploadFile) {
                // process the file
            }*/

            // Only upload the file if there was an actual file attached
            /*if($professionalUser->getPhoto()) {
                $professionalUser->getPhoto()->preUpload();
            }*/

            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $professionalUser
        ]);
    }

}