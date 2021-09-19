<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\ProfessionalVideo;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\Filter\ProfessionalFilterType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProfessionalUserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
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
 *
 * @package App\Controller
 * @Route("/dashboard")
 */
class ProfessionalController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/professionals", name="professional_index", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        $professionalUsers = $this->professionalUserRepository->getAll();

        $user = $this->getUser();

        return $this->render(
            'professionals/index.html.twig', [
                                               'user'              => $user,
                                               'professionalUsers' => $professionalUsers,
                                           ]
        );
    }

    /**
     * @Route("/professionals/results", name="professional_results_page", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function professionalsResultsAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(
            ProfessionalFilterType::class, null, [
                                             'method' => 'GET',
                                         ]
        );

        $form->handleRequest($request);

        $useRegionFiltering = false;
      /*  $regions            = [];
        if ($user->isSchoolAdministrator()) {

            $useRegionFiltering = true;

            foreach ($user->getSchools() as $school) {

                if (!$school->getRegion()) {
                    continue;
                }

                $regions[] = $school->getRegion()->getId();
            }
        }

        if ($user->isProfessional()) {

            $useRegionFiltering = true;

            foreach ($user->getRegions() as $region) {

                $regions[] = $region->getId();
            }
        }

        if ($user->isStudent() || $user->isEducator()) {

            $useRegionFiltering = true;

            if ($user->getSchool() && $user->getSchool()->getRegion()) {
                $regions[] = $user->getSchool()->getRegion()->getId();
            }
        }

        $regions = array_unique($regions);*/

        if ($useRegionFiltering) {
            $filterBuilder = $this->professionalUserRepository->createQueryBuilder('u')
                                                              ->leftJoin('u.rolesWillingToFulfill', 'rolesWillingToFulfill')
                                                              ->leftJoin('u.regions', 'regions')
                                                              ->andWhere('rolesWillingToFulfill.name LIKE :virtual OR regions.id IN (:regions)')
                                                              ->andWhere('u.deleted = 0')
                                                              ->setParameter('virtual', '%virtual%')
                                                              ->setParameter('regions', $regions)
                                                              ->addOrderBy('u.firstName', 'ASC');
        } else {

            $filterBuilder = $this->professionalUserRepository->createQueryBuilder('u')
                                                              ->andWhere('u.deleted = 0')
                                                              ->addOrderBy('u.firstName', 'ASC');
        }

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
            'professionals/results.html.twig', [
                                                 'user'         => $user,
                                                 'pagination'   => $pagination,
                                                 'form'         => $form->createView(),
                                                 'zipcode'      => $request->query->get('zipcode', ''),
                                                 'clearFormUrl' => $this->generateUrl('professional_results_page'),
                                             ]
        );
    }
}