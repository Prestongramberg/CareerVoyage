<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\StateCoordinator;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\SchoolRepository;
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
 * Class RegionalCoordinatorController
 * @package App\Controller
 * @Route("/dashboard")
 */
class RegionalCoordinatorController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @Security("is_granted('ROLE_REGIONAL_COORDINATOR_USER')")
     *
     * @Route("/reports", name="dashboard_reports")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function reportAction(Request $request) {

        /** @var RegionalCoordinator $user */
        $user = $this->getUser();

        if(!$request->query->has('type')) {
            throw new \Exception("A report type must be provided.");
        }


        switch ($request->query->get('type')) {
            case 'professionals':
                $data = $this->professionalUserRepository->fetchAll();
                break;
            case 'educators':
                $data = $this->educatorUserRepository->fetchAll();
                break;
            case 'school_administrators':
                $data = $this->schoolAdministratorRepository->fetchAll();
                break;
            default:
                throw new \Exception("Invalid report type.");
                break;
        }

        $response = new Response($this->serializer->encode($data, 'csv'));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$this->generateRandomString()}.csv");

        return $response;
    }
}
