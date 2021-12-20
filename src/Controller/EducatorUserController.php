<?php

namespace App\Controller;

use App\Entity\EducatorUser;
use App\Entity\EducatorVideo;
use App\Entity\School;
use App\Entity\User;
use App\Form\Filter\EducatorFilterType;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EducatorUserController
 *
 * @package App\Controller
 * @Route("/dashboard/educators")
 */
class EducatorUserController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @Route("/{id}/industries/add", name="educator_industry_add")
     * @param Request      $request
     * @param EducatorUser $educatorUser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addIndustry(Request $request, EducatorUser $educatorUser)
    {

        $this->denyAccessUnlessGranted('edit', $educatorUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry   = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        if ($secondaryIndustry) {
            $educatorUser->addSecondaryIndustry($secondaryIndustry);
            $this->entityManager->persist($educatorUser);
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
     * @Route("/{id}/industries/remove", name="educator_industry_remove")
     * @param Request      $request
     * @param EducatorUser $educatorUser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeIndustry(Request $request, EducatorUser $educatorUser)
    {

        $this->denyAccessUnlessGranted('edit', $educatorUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry   = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        $educatorUser->removeSecondaryIndustry($secondaryIndustry);
        $this->entityManager->persist($educatorUser);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/industries", name="educator_industries")
     * @param Request      $request
     * @param EducatorUser $educatorUser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndustries(Request $request, EducatorUser $educatorUser)
    {

        $secondaryIndustries = $educatorUser->getSecondaryIndustries();

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

    /**
     * @Route("/", name="educator_results_page", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function educatorResultsAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(
            EducatorFilterType::class, null, [
                'method' => 'GET',
            ]
        );

        $form->handleRequest($request);

        $filterBuilder = $this->educatorUserRepository->createQueryBuilder('u')
                                                      ->leftJoin('u.school', 'school')
                                                      ->andWhere('u.deleted = 0')
                                                      ->addOrderBy('u.firstName', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $sql = $filterQuery->getSQL();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render(
            'educators/results.html.twig', [
                'user' => $user,
                'pagination' => $pagination,
                'form' => $form->createView(),
                'zipcode' => $request->query->get('zipcode', ''),
                'clearFormUrl' => $this->generateUrl('educator_results_page'),
            ]
        );
    }

    /**
     * @Route("/schools/{id}/reassign", name="educator_students_reassign", methods={"POST"})
     * @param School  $school
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function educatorStudentsReassignAction(School $school, Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $studentIds         = $request->request->get('students');
        $educatorIds        = $request->request->get('educators');
        $originalEducatorId = $request->request->get('originalEducator');
        $schoolId           = $request->request->get('school');

        $originalEducator = $this->educatorUserRepository->find($originalEducatorId);

        if ($originalEducator) {

            foreach ($originalEducator->getStudentUsers() as $studentUser) {
                if (in_array($studentUser->getId(), $studentIds)) {
                    $originalEducator->removeStudentUser($studentUser);
                }
            }

            $this->entityManager->persist($originalEducator);
        }

        $students = $this->studentUserRepository->findBy([
            'id' => $studentIds,
        ]);

        $educators = $this->educatorUserRepository->findBy([
            'id' => $educatorIds,
        ]);

        foreach ($educators as $educator) {
            foreach ($students as $student) {
                if (!$educator->hasStudentUserInClass($student)) {
                    $educator->addStudentUser($student);
                }
            }
            $this->entityManager->persist($educator);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Students successfully re-assigned');

        return $this->redirectToRoute('educators_manage', ['id' => $schoolId]);
    }


    /**
     * @Route("/educators/videos/{id}/edit", name="educator_video_edit", options = { "expose" = true })
     * @param Request       $request
     * @param EducatorVideo $video
     *
     * @return JsonResponse
     */
    public function educatorEditVideoAction(Request $request, EducatorVideo $video)
    {

        $this->denyAccessUnlessGranted('edit', $video->getEducator());

        $name    = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags    = $request->request->get('tags');

        if ($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);

            if ($tags) {
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
                    'tags' => $video->getTags(),

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
     * @Route("/educators/{id}/video/add", name="educator_video_add", options = { "expose" = true })
     * @param Request      $request
     * @param EducatorUser $educatorUser
     *
     * @return JsonResponse
     */
    public function educatorAddVideoAction(Request $request, EducatorUser $educatorUser)
    {

        $this->denyAccessUnlessGranted('edit', $educatorUser);

        $name    = $request->request->get('name');
        $videoId = $request->request->get('videoId');
        $tags    = $request->request->get('tags');

        if ($name && $videoId) {
            $video = new EducatorVideo();
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setEducator($educatorUser);

            if ($tags) {
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
                    'tags' => $video->getTags(),

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
     * @Route("/educators/videos/{id}/remove", name="educator_video_remove", options = { "expose" = true })
     * @param Request       $request
     * @param EducatorVideo $video
     *
     * @return JsonResponse
     */
    public function educatorRemoveVideoAction(Request $request, EducatorVideo $video)
    {

        $this->denyAccessUnlessGranted('edit', $video->getEducator());

        $this->entityManager->remove($video);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }
}