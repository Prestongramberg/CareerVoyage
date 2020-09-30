<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\ProfessionalVideo;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProfessionalUserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
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
 * Class ProfessionalController
 * @package App\Controller
 * @Route("/dashboard")
 */
class ProfessionalController extends AbstractController
{
    use FileHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var Packages
     */
    private $assetsManager;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var CompanyPhotoRepository
     */
    private $companyPhotoRepository;

    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

    /**
     * ProfessionalController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param ProfessionalUserRepository $professionalUserRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader, UserPasswordEncoderInterface $passwordEncoder, ImageCacheGenerator $imageCacheGenerator, UploaderHelper $uploaderHelper, Packages $assetsManager, CompanyRepository $companyRepository, CompanyPhotoRepository $companyPhotoRepository, ProfessionalUserRepository $professionalUserRepository)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
        $this->professionalUserRepository = $professionalUserRepository;
    }


    /**
     * @Route("/professionals", name="professional_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $professionalUsers = $this->professionalUserRepository->getAll();

        $user = $this->getUser();
        return $this->render('professionals/index.html.twig', [
            'user' => $user,
            'professionalUsers' => $professionalUsers
        ]);
    }

    /**
     * @Route("/professionals/videos/{id}/edit", name="professional_video_edit", options = { "expose" = true })
     * @param Request $request
     * @param ProfessionalVideo $video
     * @return JsonResponse
     */
    public function professionalEditVideoAction(Request $request, ProfessionalVideo $video) {

        $this->denyAccessUnlessGranted('edit', $video->getProfessional());

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags = $request->request->get('tags');

        if($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);

            if($tags) {
                $video->setTags($tags);
            }


            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

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
     * @Route("/professionals/{id}/video/add", name="professional_video_add", options = { "expose" = true })
     * @param Request $request
     * @param ProfessionalUser $professionalUser
     * @return JsonResponse
     */
    public function professionalAddVideoAction(Request $request, ProfessionalUser $professionalUser) {

        $this->denyAccessUnlessGranted('edit', $professionalUser);

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags = $request->request->get('tags');

        if($name && $videoId) {
            $video = new ProfessionalVideo();
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setProfessional($professionalUser);

            if($tags) {
                $video->setTags($tags);
            }

            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

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
     * @Route("/professionals/videos/{id}/remove", name="professional_video_remove", options = { "expose" = true })
     * @param Request $request
     * @param ProfessionalVideo $video
     * @return JsonResponse
     */
    public function professionalRemoveVideoAction(Request $request, ProfessionalVideo $video) {

        $this->denyAccessUnlessGranted('edit', $video->getProfessional());

        $this->entityManager->remove($video);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }
}