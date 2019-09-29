<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonFavorite;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyRepository;
use App\Repository\ExperienceRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\SchoolExperienceRepository;
use App\Service\FileUploader;
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
 * Class ExperienceController
 * @package App\Controller
 * @Route("/api")
 */
class ExperienceController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/experiences", name="get_experiences", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     */
    public function getExperiences(Request $request) {

        $loggedInUser = $this->getUser();

        $schoolExperiences = [];
        $companyExperiences = $this->companyExperienceRepository->findAll();
        $teachLessonExperiences = [];

        $userId = $request->query->get('userId', null);
        $schoolId = $request->query->get('schoolId', null);
        /** @var User $user */
        if($userId && $user = $this->userRepository->find($userId)) {
            if($user->isSchoolAdministrator()) {
                /** @var SchoolAdministrator $user */
                foreach($user->getSchools() as $school) {
                    $schoolExperiences = array_merge($schoolExperiences, $this->schoolExperienceRepository->findBy(['school' => $school]));
                }
            } elseif ($user->isEducator()) {
                /** @var EducatorUser $user */
                $schoolExperiences = array_merge($schoolExperiences, $this->schoolExperienceRepository->findBy(['school' => $user->getSchool()]));
            } elseif ($user->isStudent()) {
                /** @var StudentUser $user */
                $schoolExperiences = array_merge($schoolExperiences, $this->schoolExperienceRepository->findBy(['school' => $user->getSchool()]));
            } elseif ($user->isProfessional()) {
                /** @var ProfessionalUser $user */

                $teachLessonExperiences = $this->teachLessonExperienceRepository->createQueryBuilder('tle')
                    ->andWhere('tle.teacher = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getResult();

            }
        } elseif ($schoolId && $school = $this->schoolRepository->find($schoolId)) {

            // show the events where a professional is coming in to teach
            $teachLessonExperiences = $this->teachLessonExperienceRepository->findBy([
                'school' => $school
            ]);

            $schoolExperiences = array_merge($schoolExperiences, $this->schoolExperienceRepository->findBy(['school' => $school]));
        }

        $experiences = array_merge($schoolExperiences, $companyExperiences, $teachLessonExperiences);

        $json = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA', 'ALL_USER_DATA']]);
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
     * @Route("/experiences/{id}/remove", name="remove_experience", methods={"POST"}, options = { "expose" = true })
     * @param Experience $experience
     * @param Request $request
     * @return JsonResponse
     */
    public function removeExperience(Experience $experience, Request $request) {

        $this->denyAccessUnlessGranted('edit', $experience);

        $this->entityManager->remove($experience);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true
            ],
            Response::HTTP_OK
        );
    }
}