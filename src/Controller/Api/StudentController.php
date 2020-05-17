<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonRepository;
use App\Repository\ProfessionalUserRepository;
use App\Service\FileUploader;
use App\Service\Geocoder;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
 * Class StudentController
 * @package App\Controller
 * @Route("/api")
 */
class StudentController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/students", name="get_students", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfessionals(Request $request) {

        $schoolId = $request->query->get('school', null);

        if($schoolId) {
            $students = $this->studentUserRepository->findBy([
                'school' => $schoolId
            ]);
        } else {
            $students = $this->studentUserRepository->findAll();
        }

        $json = $this->serializer->serialize($students, 'json', ['groups' => ['ALL_USER_DATA']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload
            ],
            Response::HTTP_OK
        );
    }
}