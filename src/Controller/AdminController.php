<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EventTypeFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RolesFormType;
use App\Form\SiteAdminFormType;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
 * Class AdminController
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newRole(Request $request) {

        $user = $this->getUser();
        $role = new RolesWillingToFulfill();

        $form = $this->createForm(RolesFormType::class, $role, [
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var $role RolesWillingToFulfill */
            $role = $form->getData();
            $role->setInRoleDropdown(true);
            $role->setEventName($role->getName());

            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $this->addFlash('success', 'New role has been created.');
            return $this->redirectToRoute('admin_role_new');
        }

        return $this->render('admin/new_role.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/event-types/new", name="admin_event_types_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newEventType(Request $request) {

        $user = $this->getUser();
        $role = new RolesWillingToFulfill();

        $form = $this->createForm(EventTypeFormType::class, $role, [
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var $role RolesWillingToFulfill */
            $role = $form->getData();
            $role->setEventName($role->getName());

            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $this->addFlash('success', 'New experience type has been created.');
            return $this->redirectToRoute('admin_role_new');
        }

        return $this->render('admin/new_event_type.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}