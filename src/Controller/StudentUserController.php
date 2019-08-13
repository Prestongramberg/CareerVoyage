<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\StudentUser;
use App\Entity\User;
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
 * Class StudentUserController
 * @package App\Controller
 * @Route("/dashboard/students")
 */
class StudentUserController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @Route("/{id}/industries/add", name="student_industry_add")
     * @param Request $request
     * @param StudentUser $studentUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addIndustry(Request $request, StudentUser $studentUser) {

        $this->denyAccessUnlessGranted('edit', $studentUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        if($secondaryIndustry) {
            $studentUser->addSecondaryIndustry($secondaryIndustry);
            $this->entityManager->persist($studentUser);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/industries/remove", name="student_industry_remove")
     * @param Request $request
     * @param StudentUser $studentUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeIndustry(Request $request, StudentUser $studentUser) {

        $this->denyAccessUnlessGranted('edit', $studentUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        $studentUser->removeSecondaryIndustry($secondaryIndustry);
        $this->entityManager->persist($studentUser);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/industries", name="student_industries")
     * @param Request $request
     * @param StudentUser $studentUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndustries(Request $request, StudentUser $studentUser) {

        $secondaryIndustries = $studentUser->getSecondaryIndustries();

        $json = $this->serializer->serialize($secondaryIndustries, 'json', ['groups' => ['RESULTS_PAGE']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

}