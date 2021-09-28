<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\SiteAdminUser;
use App\Form\SiteAdminFormType;
use App\Form\CourseFormType;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SiteController
 * @package App\Controller
 * @Route("/dashboard/sites")
 */
class SiteController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @IsGranted("ROLE_ADMIN_USER")
     * @Route("/admin/new", name="sites_admin_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newAdminUser(Request $request) {

        $user = $this->getUser();
        $siteAdmin = new SiteAdminUser();

        $form = $this->createForm(SiteAdminFormType::class, $siteAdmin, [
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var SiteAdminUser $siteAdmin */
            $siteAdmin = $form->getData();

            $existingUser = $this->userRepository->findOneBy(['email' => $siteAdmin->getEmail()]);
            // for now just skip users that are already in the system
            if($existingUser) {
                $this->addFlash('error', 'This user already exists in the system');
                return $this->redirectToRoute('sites_admin_new');
            } else {
                $siteAdmin->initializeNewUser(false, true);
                $siteAdmin->setPasswordResetToken();
                $siteAdmin->setupAsSiteAdminUser();
                $this->entityManager->persist($siteAdmin);
            }

            $this->entityManager->flush();
            $this->securityMailer->sendPasswordSetupForSiteAdmin($siteAdmin);
            $this->addFlash('success', 'Site admin invite sent.');
            return $this->redirectToRoute('sites_admin_new');
        }

        return $this->render('site/newSiteAdmin.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

     /**
     * @IsGranted("ROLE_ADMIN_USER")
     * @Route("/admin/new_course", name="add_course")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newCourse(Request $request) {

        $user = $this->getUser();
        $course = new Course();

        $form = $this->createForm(CourseFormType::class, $course, [
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Course $course */
            $course = $form->getData();

            // $course->setupAsSiteAdminUser();
            $this->entityManager->persist($course);


            $this->entityManager->flush();
            $this->addFlash('success', 'New course added');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('site/addNewCourse.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

}