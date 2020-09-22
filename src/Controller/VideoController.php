<?php

namespace App\Controller;

use App\Entity\CareerVideo;
use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\CompanyVideo;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\Experience;
use App\Entity\ExperienceFile;
use App\Entity\HelpVideo;
use App\Entity\Image;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\Registration;
use App\Entity\RequestPossibleApprovers;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\Video;
use App\Form\CompanyInviteFormType;
use App\Form\EditCompanyExperienceType;
use App\Form\EditCompanyFormType;
use App\Form\EducatorRegisterStudentsForExperienceFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewCompanyExperienceType;
use App\Form\ProfessionalEditProfileFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Mailer\ExperienceMailer;
use App\Repository\AdminUserRepository;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\HelpVideoRepository;
use App\Repository\JoinCompanyRequestRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Psr\Log\LoggerInterface;

/**
 * Class VideoController
 * @package App\Controller
 * @Route("/dashboard")
 */
class VideoController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/videos", name="video_index", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $editVideoId = $request->query->get('editVideo', null);
        $careerVideo = null;
        if($editVideoId) {
            $careerVideo = $this->careerVideoRepository->find($editVideoId);
        }

        $user = $this->getUser();
        return $this->render('video/index.html.twig', [
            'user' => $user,
            'careerVideo' => $careerVideo
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/career-videos/add", name="career_videos_add", options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     */
    public function careerAddVideoAction(Request $request) {

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags = $request->request->get('tags');
        $secondaryIndustryIds = $request->request->get('secondaryIndustries');

        if(!empty($secondaryIndustryIds)) {
            $secondaryIndustries = $this->secondaryIndustryRepository->findBy([
                'id' => $secondaryIndustryIds
            ]);
        } else {
            $secondaryIndustries = [];
        }

        if($name && $videoId) {
            $video = new CareerVideo();
            $video->setName($name);
            $video->setVideoId($videoId);

            if($tags) {
                $video->setTags($tags);
            }

            foreach($secondaryIndustries as $secondaryIndustry) {
                $video->addSecondaryIndustry($secondaryIndustry);
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
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/career-videos/{id}/edit", name="career_videos_edit", options = { "expose" = true })
     * @param Request $request
     * @param CareerVideo $video
     * @return JsonResponse
     */
    public function careerEditVideoAction(Request $request, CareerVideo $video) {

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags = $request->request->get('tags');
        $secondaryIndustryIds = $request->request->get('secondaryIndustries');

        if(!empty($secondaryIndustryIds)) {
            $secondaryIndustries = $this->secondaryIndustryRepository->findBy([
                'id' => $secondaryIndustryIds
            ]);
        } else {
            $secondaryIndustries = [];
        }

        $secondaryIndustries = new ArrayCollection($secondaryIndustries);

        $originalSecondaryIndustries = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($video->getSecondaryIndustries() as $secondaryIndustry) {
            $originalSecondaryIndustries->add($secondaryIndustry);
        }

        if($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);

            if($tags) {
                $video->setTags($tags);
            }

            foreach ($originalSecondaryIndustries as $originalSecondaryIndustry) {
                if (false === $secondaryIndustries->contains($originalSecondaryIndustry)) {
                    $video->removeSecondaryIndustry($originalSecondaryIndustry);
                }
            }

            foreach($secondaryIndustries as $secondaryIndustry) {
                if (false === $video->getSecondaryIndustries()->contains($secondaryIndustry)) {
                    $video->addSecondaryIndustry($secondaryIndustry);
                }
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
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/career-videos/{id}/delete", name="career_videos_delete", options = { "expose" = true })
     * @param Request $request
     * @param CareerVideo $video
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function careerDeleteVideoAction(Request $request, CareerVideo $video) {

        $this->entityManager->remove($video);
        $this->entityManager->flush();

        $this->addFlash('success', 'Video successfully removed');

        return $this->redirectToRoute('video_index');
    }


    /**
     * @Route("/admin-videos", name="admin_videos", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminVideosAction(Request $request) {

        $userRoles = [
            ["ANY", "Any User Role"],
            ["ROLE_PROFESSIONAL_USER", "Professional"],
            ["ROLE_EDUCATOR_USER", "Educator"],
            ["ROLE_STUDENT_USER", "Student"],
            ["ROLE_SCHOOL_ADMINISTRATOR_USER", "School Administrator"],
            ["ROLE_REGIONAL_COORDINATOR_USER", "Region Coordinator"],
            ["ROLE_STATE_COORDINATOR_USER", "State Coordinator"]
        ];

        $editVideoId = $request->query->get('editVideo', null);
        $helpVideo = null;
        if($editVideoId) {
            $helpVideo = $this->helpVideoRepository->find($editVideoId);
        }

        $videos = [];
        foreach($userRoles as $k => $v){
            $videos[$v[0]] = $this->helpVideoRepository->findBy(
                ['userRole' => $v[0]],
                ['position' => 'ASC']
            );
        }

        $user = $this->getUser();
        return $this->render('video/admin_index.html.twig', [
            'user' => $user,
            'helpVideo' => $helpVideo,
            'roles' => $userRoles,
            'videos' => $videos
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/admin-videos/add", name="admin_videos_add", options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     */
    public function adminAddVideoAction(Request $request) {

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $userRole = $request->request->get('userRole');

        if($name && $videoId) {
            $video = new HelpVideo();
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setUserRole($userRole);

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
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/admin-videos/{id}/edit", name="admin_videos_edit", options = { "expose" = true })
     * @param Request $request
     * @param Video $video
     * @return JsonResponse
     */
    public function adminEditVideoAction(Request $request, Video $video) {

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $userRole = $request->request->get('userRole');

        if($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setUserRole($userRole);

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
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/admin-videos/{id}/delete", name="admin_videos_delete", options = { "expose" = true })
     * @param Request $request
     * @param Video $video
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function adminDeleteVideoAction(Request $request, Video $video) {
        if($video) {
            $this->entityManager->remove($video);
            $this->entityManager->flush();

            // $this->addFlash('success', 'Video successfully removed');

            // return $this->redirectToRoute('video_index');
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
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER"})
     * @Route("/admin-videos/{role}/sort", name="admin_videos_sort", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function adminSortVideosAction(Request $request) {
        
        $list = $request->request->get('data');
        if($list) {
            parse_str($list, $list_array);

            foreach($list_array['item'] as $k => $v){

                $em = $this->getDoctrine()->getManager();
                $video = $em->getRepository('App:HelpVideo')->find($v);

                if($video) {
                    $video->setPosition($k);

                    $em->persist($video);
                    $em->flush();
                }
                
            }

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



}
