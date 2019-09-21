<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\User;
use App\Form\CreateRegionFormType;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RegionalCoordinatorFormType;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StateCoordinatorRequestRepository;
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
 * Class RegionController
 * @package App\Controller
 * @Route("/dashboard/regions")
 */
class RegionController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @IsGranted({"ROLE_STATE_COORDINATOR_USER"})
     * @Route("/new", name="region_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newRegion(Request $request) {

        /** @var StateCoordinator $user */
        $user = $this->getUser();
        $region = new Region();

        $form = $this->createForm(CreateRegionFormType::class, $region, [
            'method' => 'POST',
            'loggedInUser' => $user
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Region $region */
            $region = $form->getData();
            $region->setSite($user->getSite());
            $region->setState($user->getState());

            $this->entityManager->persist($region);
            $this->entityManager->flush();

            $this->addFlash('success', 'Region successfully created.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('region/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Security("is_granted('ROLE_STATE_COORDINATOR_USER')")
     * @Route("/coordinator/new", name="regional_coordinator_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newCoordinatorAction(Request $request) {

        /** @var StateCoordinator $user */
        $user = $this->getUser();
        $regionalCoordinator = new RegionalCoordinator();

        $form = $this->createForm(RegionalCoordinatorFormType::class, $regionalCoordinator, [
            'method' => 'POST',
            'site' => $user->getSite()
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var RegionalCoordinator $regionalCoordinator */
            $regionalCoordinator = $form->getData();

            $existingUser = $this->userRepository->findOneBy(['email' => $regionalCoordinator->getEmail()]);
            // for now just skip users that are already in the system
            if($existingUser) {
                $this->addFlash('error', 'This user already exists in the system');
                return $this->redirectToRoute('regional_coordinator_new');
            } else {
                $regionalCoordinator->initializeNewUser(false, true);
                $regionalCoordinator->setPasswordResetToken();
                $regionalCoordinator->setupAsRegionalCoordinator();
                $regionalCoordinator->setSite($user->getSite());
                $this->entityManager->persist($regionalCoordinator);
            }

            $this->entityManager->flush();
            $this->securityMailer->sendPasswordSetupForRegionalCoordinator($regionalCoordinator);

            $this->addFlash('success', 'Regional coordinator invite sent.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('regionalCoordinator/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
