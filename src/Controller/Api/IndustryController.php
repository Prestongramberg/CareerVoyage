<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyFavorite;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Entity\Video;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyFavoriteRepository;
use App\Repository\CompanyRepository;
use App\Repository\CourseRepository;
use App\Repository\IndustryRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
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
 * Class IndustryController
 * @package App\Controller
 * @Route("/api")
 */
class IndustryController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/industries", name="get_industries", methods={"GET"}, options = { "expose" = true })
     */
    public function getIndustries() {

        $industries = $this->industryRepository->findAll();

        $json = $this->serializer->serialize($industries, 'json', ['groups' => ['RESULTS_PAGE']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/secondary_industries", name="get_secondary_industries", methods={"GET"}, options = { "expose" = true })
     */
    public function getSecondaryIndustries() {
        $secondaryIndustries = $this->secondaryIndustryRepository->findBy(array(), array('name' => 'ASC'));
        
        $json = $this->serializer->serialize($secondaryIndustries, 'json', ['groups' => ['RESULTS_PAGE']]);

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