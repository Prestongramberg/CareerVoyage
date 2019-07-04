<?php

namespace App\Controller;

use App\Entity\ProfessionalUser;
use App\Form\ProfessionalEditProfileFormType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * ProfileController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     */
    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
    }

    /**
     * @Route("/profile", name="profile_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        return $this->render('profile/index.html.twig', []);
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

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var ProfessionalUser $professionalUser */
            $professionalUser = $form->getData();
            $professionalUser->getPhoto()->preUpload();
            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

}