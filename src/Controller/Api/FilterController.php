<?php

namespace App\Controller\Api;

use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FilterController
 *
 * @package App\Controller
 * @Route("/api/filters")
 */
class FilterController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="get_filters", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFilters(Request $request)
    {
        $loggedInUser       = $this->getUser();

       /* $industries = $this->industryRepository->findAll();
        $secondaryIndustries = $this->secondaryIndustryRepository->findAll();
        $eventTypes = $this->rolesWillingToFulfillRepository->findAll();

        $json    = $this->serializer->serialize(
            $experiences, 'json', [
                            'groups' => [
                                'EXPERIENCE_DATA',
                                'ALL_USER_DATA',
                            ],
                        ]
        );
        $payload = json_decode($json, true);*/

        return new JsonResponse(
            [
                'success' => true,
                /*'data'    => $payload,*/
            ],
            Response::HTTP_OK
        );
    }
}
