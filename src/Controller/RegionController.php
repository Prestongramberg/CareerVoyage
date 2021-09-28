<?php

namespace App\Controller;

use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\StateCoordinator;
use App\Form\CreateRegionFormType;
use App\Form\EditRegionFormType;
use App\Form\RegionalCoordinatorFormType;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @IsGranted({"ROLE_STATE_COORDINATOR_USER"})
     * @Route("/{id}/edit", name="region_edit", options = { "expose" = true })
     * @param Request $request
     * @param Region $region
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function editRegionAction(Request $request, Region $region) {
        /** @var StateCoordinator $user */
        $user = $this->getUser();
        
        $form = $this->createForm(EditRegionFormType::class, $region, [
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

            $this->addFlash('success', 'Region successfully saved.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('region/edit.html.twig', [
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
