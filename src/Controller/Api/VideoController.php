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
use App\Entity\VideoFavorite;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyFavoriteRepository;
use App\Repository\CompanyRepository;
use App\Repository\CourseRepository;
use App\Repository\IndustryRepository;
use App\Service\FileUploader;
use App\Service\Geocoder;
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
 * Class VideoController
 * @package App\Controller
 * @Route("/api")
 */
class VideoController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/videos", name="api_get_videos", methods={"GET"}, options = { "expose" = true })
     */
    public function getVideos() {

        /** @var User $user */
        $user = $this->getUser();

        $companyVideos = $this->companyVideoRepository->findAll();

        foreach($companyVideos as $companyVideo) {

            $favoriteVideo = $this->videoFavoriteRepository->findOneBy([
                'video' => $companyVideo,
                'user' => $user
            ]);

            if($favoriteVideo) {
                $companyVideo->setIsFavorite(true);
            } else {
                $companyVideo->setIsFavorite(false);
            }
        }


        $careerVideos = $this->careerVideoRepository->findAll();

        foreach($careerVideos as $careerVideo) {

            $favoriteVideo = $this->videoFavoriteRepository->findOneBy([
                'video' => $careerVideo,
                'user' => $user
            ]);

            if($favoriteVideo) {
                $careerVideo->setIsFavorite(true);
            } else {
                $careerVideo->setIsFavorite(false);
            }
        }

        $professionalVideos = $this->professionalVideoRepository->findAll();

        foreach($professionalVideos as $professionalVideo) {

            $favoriteVideo = $this->videoFavoriteRepository->findOneBy([
                'video' => $professionalVideo,
                'user' => $user
            ]);

            if($favoriteVideo) {
                $professionalVideo->setIsFavorite(true);
            } else {
                $professionalVideo->setIsFavorite(false);
            }
        }

        $allVideos = [];
        foreach($companyVideos as $v) {
            array_push($allVideos, $v);
        }
        foreach($careerVideos as $v) {
            array_push($allVideos, $v);
        }
        foreach($professionalVideos as $v) {
            array_push($allVideos, $v);
        }
        


        $allVideosJson = $this->serializer->serialize($allVideos, 'json', ['groups' => ['VIDEO']]);
        $companyVideosJson = $this->serializer->serialize($companyVideos, 'json', ['groups' => ['VIDEO']]);
        $careerVideosJson = $this->serializer->serialize($careerVideos, 'json', ['groups' => ['VIDEO']]);
        $professionalVideosJson = $this->serializer->serialize($professionalVideos, 'json', ['groups' => ['VIDEO', 'PROFESSIONAL_USER_DATA']]);

        return new JsonResponse(
            [
                'success' => true,
                'data' => [
                    'companyVideos' => json_decode($companyVideosJson, true),
                    'careerVideos' => json_decode($careerVideosJson, true),
                    'professionalVideos' => json_decode($professionalVideosJson, true),
                    'allVideos' => json_decode($allVideosJson, true)
                ]
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/videos/{id}/favorite", name="favorite_video", methods={"POST"}, options = { "expose" = true })
     * @param Video $video
     * @return JsonResponse
     */
    public function favoriteVideo(Video $video) {

        $videoFavoriteObj = $this->videoFavoriteRepository->findOneBy([
            'user' => $this->getUser(),
            'video' => $video
        ]);

        if($videoFavoriteObj) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'video has already been added to favorites.'

                ],
                Response::HTTP_OK
            );
        }

        $videoFavorite = new VideoFavorite();
        $videoFavorite->setUser($this->getUser());
        $videoFavorite->setVideo($video);

        $this->entityManager->persist($videoFavorite);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'video added to favorites.'
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/videos{id}/unfavorite", name="unfavorite_video", methods={"POST"}, options = { "expose" = true })
     * @param Video $video
     * @return JsonResponse
     */
    public function unFavoriteVideo(Video $video) {

        $videoFavoriteObj = $this->videoFavoriteRepository->findOneBy([
            'user' => $this->getUser(),
            'video' => $video
        ]);

        if($videoFavoriteObj) {
            $this->entityManager->remove($videoFavoriteObj);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'message' => 'video removed from favorites.'
                ],
                Response::HTTP_OK
            );
        }


        return new JsonResponse(
            [
                'success' => false,
                'message' => 'video cannot be removed from favorites cause it does not exist in favorites'
            ],
            Response::HTTP_OK
        );
    }
}