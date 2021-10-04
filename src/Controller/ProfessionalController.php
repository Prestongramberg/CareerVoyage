<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Filter\ProfessionalFilterType;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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