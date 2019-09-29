<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\User;
use App\Form\AdminProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\RegionalCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class DashboardController
 * @package App\Controller
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="dashboard", methods={"GET"})
     * @param Request $request
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, SessionInterface $session) {

        /** @var User $user */
        $user = $this->getUser();

        $dashboards = [];

        if($user->isRegionalCoordinator()) {
            /** @var RegionalCoordinator $user */
            $numberOfStudentsInRegion = count($this->studentUserRepository->getStudentsForRegion($user->getRegion()));
            $numberOfEducatorsInRegion = count($this->educatorUserRepository->getEducatorsForRegion($user->getRegion()));
            $numberOfSchoolAdminsInRegion = count($this->schoolAdministratorRepository->getSchoolAdminsForRegion($user->getRegion()));
            $schoolEventsByRegionGroupedBySchool = $this->schoolExperienceRepository->getEventsByRegionGroupedBySchool($user->getRegion());
            $companyEventsGroupedByPrimaryIndustry = $this->companyExperienceRepository->getEventsGroupedByPrimaryIndustry();
            $numberOfRegistrationsGroupedByPrimaryIndustry = $this->companyExperienceRepository->getNumberOfRegistrationsGroupedByPrimaryIndustry();

            $dashboards = [
                'numberOfStudentsInRegion' => $numberOfStudentsInRegion,
                'numberOfEducatorsInRegion' => $numberOfEducatorsInRegion,
                'numberOfSchoolAdminsInRegion' => $numberOfSchoolAdminsInRegion,
                'schoolEventsByRegionGroupedBySchool' => $schoolEventsByRegionGroupedBySchool,
                'companyEventsGroupedByPrimaryIndustry' => $companyEventsGroupedByPrimaryIndustry,
                'numberOfRegistrationsGroupedByPrimaryIndustry' => $numberOfRegistrationsGroupedByPrimaryIndustry
            ];
        } elseif ($user->isSchoolAdministrator()) {
            /** @var SchoolAdministrator $user */
            $numberOfStudentsInSchoolNetwork = 0;
            $numberOfEducatorsInSchoolNetwork = 0;
            $dashboards['registrationsGroupedByPrimaryIndustryInSchool'] = [];
            foreach($user->getSchools() as $school) {
                $numberOfStudentsInSchoolNetwork += count($this->studentUserRepository->findBy(['school' => $school]));
                $numberOfEducatorsInSchoolNetwork+= count($this->educatorUserRepository->findBy(['school' => $school]));

                $registrationsGroupedByPrimaryIndustryInSchool = $this->companyExperienceRepository->getNumberOfRegistrationsGroupedByPrimaryIndustryInSchool($school);

                $dashboards['registrationsGroupedByPrimaryIndustryInSchool'][$school->getId()] = [
                    'schoolName' => $school->getName(),
                    'school_id' => $school->getId(),
                    'registrationsGroupedByPrimaryIndustryInSchool' => $registrationsGroupedByPrimaryIndustryInSchool
                ];
            }

            $dashboards = [
                'numberOfStudentsInSchoolNetwork' => $numberOfStudentsInSchoolNetwork,
                'numberOfEducatorsInSchoolNetwork' => $numberOfEducatorsInSchoolNetwork
            ];
        }

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'dashboards' => $dashboards
        ]);
    }
}