<?php

namespace App\Controller;

use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentUser;
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
 * @Route("/dashboard/salary-exploration")
 */
class SalaryExplorationController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/primary-industries", name="salary_exploration_primary_industry", methods={"GET"})
     * @param Request $request
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function primaryIndustryAction(Request $request, SessionInterface $session) {

        /** @var User $user */
        $user = $this->getUser();

        $primaryIndustries = $this->industryRepository->findAll();

        return $this->render('salaryExploration/primary_industry.html.twig', [
            'user' => $user,
            'primaryIndustries' => $primaryIndustries
        ]);
    }

    /**
     * @Route("/primary-industries/{id}/secondary-industries", name="salary_exploration_secondary_industry", methods={"GET"})
     * @param Request $request
     * @param Industry $primaryIndustry
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function secondaryIndustryAction(Request $request, Industry $primaryIndustry) {

        /** @var User $user */
        $user = $this->getUser();

        $secondaryIndustries = $this->secondaryIndustryRepository->findBy([
            'primaryIndustry' => $primaryIndustry
        ]);

        return $this->render('salaryExploration/secondary_industry.html.twig', [
            'user' => $user,
            'secondaryIndustries' => $secondaryIndustries,
            'primaryIndustry' => $primaryIndustry
        ]);
    }

    /**
     * @Route("/secondary-industries/{id}/details", name="salary_exploration_details", methods={"GET"})
     * @param Request $request
     * @param SecondaryIndustry $secondaryIndustry
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction(Request $request, SecondaryIndustry $secondaryIndustry) {

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('salaryExploration/details.html.twig', [
            'user' => $user,
            'secondaryIndustry' => $secondaryIndustry
        ]);
    }
}