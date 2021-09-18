<?php

namespace App\Controller\Api;

use App\Entity\CompanyVideo;
use App\Entity\EducatorVideo;
use App\Entity\ProfessionalUser;
use App\Entity\ProfessionalVideo;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoFavorite;
use App\Form\VideoType;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class VideoController
 *
 * @package App\Controller
 * @Route("/api/videos")
 */
class VideoController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="api_get_videos", methods={"GET"}, options = { "expose" = true })
     */
    public function getVideos()
    {

        /** @var User $user */
        $user = $this->getUser();

        $companyVideos = $this->companyVideoRepository->findAll();

        foreach ($companyVideos as $companyVideo) {

            $favoriteVideo = $this->videoFavoriteRepository->findOneBy([
                'video' => $companyVideo,
                'user' => $user,
            ]);

            if ($favoriteVideo) {
                $companyVideo->setIsFavorite(true);
            } else {
                $companyVideo->setIsFavorite(false);
            }
        }


        $careerVideos = $this->careerVideoRepository->findAll();

        foreach ($careerVideos as $careerVideo) {

            $favoriteVideo = $this->videoFavoriteRepository->findOneBy([
                'video' => $careerVideo,
                'user' => $user,
            ]);

            if ($favoriteVideo) {
                $careerVideo->setIsFavorite(true);
            } else {
                $careerVideo->setIsFavorite(false);
            }
        }

        $professionalVideos = $this->professionalVideoRepository->findAll();

        foreach ($professionalVideos as $professionalVideo) {

            $favoriteVideo = $this->videoFavoriteRepository->findOneBy([
                'video' => $professionalVideo,
                'user' => $user,
            ]);

            if ($favoriteVideo) {
                $professionalVideo->setIsFavorite(true);
            } else {
                $professionalVideo->setIsFavorite(false);
            }
        }

        $allVideos = [];
        foreach ($companyVideos as $v) {
            array_push($allVideos, $v);
        }
        foreach ($careerVideos as $v) {
            array_push($allVideos, $v);
        }
        foreach ($professionalVideos as $v) {
            array_push($allVideos, $v);
        }


        $allVideosJson          = $this->serializer->serialize($allVideos, 'json', ['groups' => ['VIDEO']]);
        $companyVideosJson      = $this->serializer->serialize($companyVideos, 'json', ['groups' => ['VIDEO']]);
        $careerVideosJson       = $this->serializer->serialize($careerVideos, 'json', ['groups' => ['VIDEO']]);
        $professionalVideosJson = $this->serializer->serialize($professionalVideos, 'json', ['groups' => ['VIDEO',
                                                                                                          'PROFESSIONAL_USER_DATA',
        ],
        ]);

        return new JsonResponse(
            [
                'success' => true,
                'data' => [
                    'companyVideos' => json_decode($companyVideosJson, true),
                    'careerVideos' => json_decode($careerVideosJson, true),
                    'professionalVideos' => json_decode($professionalVideosJson, true),
                    'allVideos' => json_decode($allVideosJson, true),
                ],
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/new", name="api_video_new", options = {"expose" = true })
     * @Method({"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function new(Request $request)
    {
        /** @var User $user */
        $user      = $this->getUser();
        $video     = new Video();
        $action    = $this->generateUrl('api_video_new', []);
        $dataClass = Video::class;

        if ($request->query->has('companyId')) {

            $companyId = $request->query->get('companyId');
            $company   = $this->companyRepository->find($companyId);

            $video = new CompanyVideo();
            $video->setCompany($company);
            $action    = $this->generateUrl('api_video_new', [
                'companyId' => $company->getId(),
            ]);
            $dataClass = CompanyVideo::class;
        }

        if ($request->query->has('professionalId')) {

            $professionalId = $request->query->get('professionalId');
            $professional   = $this->professionalUserRepository->find($professionalId);

            $video = new ProfessionalVideo();
            $video->setProfessional($professional);
            $action    = $this->generateUrl('api_video_new', [
                'professionalId' => $professional->getId(),
            ]);
            $dataClass = ProfessionalVideo::class;
        }

        if ($request->query->has('educatorId')) {

            $educatorId = $request->query->get('educatorId');
            $educator   = $this->educatorUserRepository->find($educatorId);

            $video = new EducatorVideo();
            $video->setEducator($educator);
            $action    = $this->generateUrl('api_video_new', [
                'educatorId' => $educator->getId(),
            ]);
            $dataClass = EducatorVideo::class;
        }

        $form = $this->createForm(VideoType::class, $video, [
            'action' => $action,
            'data_class' => $dataClass,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Video $video */
            $video = $form->getData();

            $this->entityManager->persist($video);
            $this->entityManager->flush();

            $editUrl   = $this->generateUrl('api_video_edit', ['id' => $video->getId()]);
            $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId()]);

            if ($request->query->has('companyId')) {
                $editUrl = $this->generateUrl('api_video_edit', ['id' => $video->getId(),
                                                                 'companyId' => $request->query->get('companyId'),
                ]);

                $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId(),
                                                                     'companyId' => $request->query->get('companyId'),
                ]);
            }

            if ($request->query->has('professionalId')) {
                $editUrl = $this->generateUrl('api_video_edit', ['id' => $video->getId(),
                                                                 'professionalId' => $request->query->get('professionalId'),
                ]);

                $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId(),
                                                                     'professionalId' => $request->query->get('professionalId'),
                ]);
            }

            if ($request->query->has('educatorId')) {
                $editUrl = $this->generateUrl('api_video_edit', ['id' => $video->getId(),
                                                                 'educatorId' => $request->query->get('educatorId'),
                ]);

                $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId(),
                                                                     'educatorId' => $request->query->get('educatorId'),
                ]);
            }

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $video->getName(),
                    'videoId' => $video->getVideoId(),
                    'editUrl' => $editUrl,
                    'deleteUrl' => $deleteUrl,

                ], Response::HTTP_OK
            );

        }

        $formMarkup = $this->renderView(
            'video/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        return new JsonResponse(
            [
                'formMarkup' => $formMarkup,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/{id}/edit", name="api_video_edit", options = {"expose" = true })
     * @Method({"GET", "POST"})
     * @param Request $request
     *
     * @param Video   $video
     *
     * @return JsonResponse
     */
    public function edit(Request $request, Video $video)
    {
        /** @var User $user */
        $user = $this->getUser();

        $action    = $this->generateUrl('api_video_edit', ['id' => $video->getId()]);
        $dataClass = Video::class;

        if ($request->query->has('companyId')) {
            $action    = $this->generateUrl('api_video_edit', [
                'id' => $video->getId(),
                'companyId' => $request->query->get('companyId'),
            ]);
            $dataClass = CompanyVideo::class;
        }

        if ($request->query->has('professionalId')) {
            $action    = $this->generateUrl('api_video_edit', [
                'id' => $video->getId(),
                'professionalId' => $request->query->get('professionalId'),
            ]);
            $dataClass = ProfessionalVideo::class;
        }

        if ($request->query->has('educatorId')) {
            $action    = $this->generateUrl('api_video_edit', [
                'id' => $video->getId(),
                'educatorId' => $request->query->get('educatorId'),
            ]);
            $dataClass = EducatorVideo::class;
        }

        $form = $this->createForm(VideoType::class, $video, [
            'action' => $action,
            'data_class' => $dataClass,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            $editUrl   = $this->generateUrl('api_video_edit', ['id' => $video->getId()]);
            $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId()]);

            if ($request->query->has('companyId')) {
                $editUrl = $this->generateUrl('api_video_edit', ['id' => $video->getId(),
                                                                 'companyId' => $request->query->get('companyId'),
                ]);

                $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId(),
                                                                     'companyId' => $request->query->get('companyId'),
                ]);
            }

            if ($request->query->has('professionalId')) {
                $editUrl = $this->generateUrl('api_video_edit', ['id' => $video->getId(),
                                                                 'professionalId' => $request->query->get('professionalId'),
                ]);

                $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId(),
                                                                     'professionalId' => $request->query->get('professionalId'),
                ]);
            }

            if ($request->query->has('educatorId')) {
                $editUrl = $this->generateUrl('api_video_edit', ['id' => $video->getId(),
                                                                 'educatorId' => $request->query->get('educatorId'),
                ]);

                $deleteUrl = $this->generateUrl('api_video_delete', ['id' => $video->getId(),
                                                                     'educatorId' => $request->query->get('educatorId'),
                ]);
            }

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $video->getName(),
                    'videoId' => $video->getVideoId(),
                    'editUrl' => $editUrl,
                    'deleteUrl' => $deleteUrl,

                ], Response::HTTP_OK
            );
        }

        $formMarkup = $this->renderView(
            'video/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        return new JsonResponse(
            [
                'formMarkup' => $formMarkup,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/{id}/delete", name="api_video_delete", options = {"expose" = true })
     * @Method({"GET", "POST"})
     * @param Request $request
     *
     * @param Video   $video
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Video $video)
    {
        /** @var User $user */
        $user = $this->getUser();

        $videoId = $video->getId();

        $this->entityManager->remove($video);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'id' => $videoId,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/favorite", name="favorite_video", methods={"GET"}, options = { "expose" = true })
     * @param Video   $video
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function favoriteVideo(Video $video, Request $request)
    {

        $videoFavoriteObj = $this->videoFavoriteRepository->findOneBy([
            'user' => $this->getUser(),
            'video' => $video,
        ]);

        $redirect = $request->query->get('redirect', 'videos_local_company');

        if ($videoFavoriteObj) {
            return $this->redirectToRoute($redirect);
        }

        $videoFavorite = new VideoFavorite();
        $videoFavorite->setUser($this->getUser());
        $videoFavorite->setVideo($video);

        $this->entityManager->persist($videoFavorite);
        $this->entityManager->flush();

        return $this->redirectToRoute($redirect);
    }

    /**
     * @Route("/{id}/unfavorite", name="unfavorite_video", methods={"GET"}, options = { "expose" = true })
     * @param Video   $video
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unFavoriteVideo(Video $video, Request $request)
    {

        $videoFavoriteObj = $this->videoFavoriteRepository->findOneBy([
            'user' => $this->getUser(),
            'video' => $video,
        ]);

        $redirect = $request->query->get('redirect', 'videos_local_company');

        if ($videoFavoriteObj) {
            $this->entityManager->remove($videoFavoriteObj);
            $this->entityManager->flush();

            return $this->redirectToRoute($redirect);
        }

        return $this->redirectToRoute($redirect);
    }
}