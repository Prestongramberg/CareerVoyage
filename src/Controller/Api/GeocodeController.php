<?php

namespace App\Controller\Api;

use App\Entity\State;
use App\Entity\Tag;
use App\Entity\User;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagController
 *
 * @package GeocodeController
 * @Route("/api/geocode")
 */
class GeocodeController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="api_geocode", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return Response
     */
    public function searchAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $addressSearch = $request->query->get('addressSearch');

        try {
            $addressComponents = $this->geocoder->getAddressComponentsFromSearchString($addressSearch);
            $state = $addressComponents['state'];
            $city = $addressComponents['city'];
            $street = $addressComponents['street'];
            $zipCode = $addressComponents['postalCode'];

            $formattedAddress = sprintf("%s %s %s %s", $street, $city, $state->getAbbreviation(), $zipCode);

            if ($coordinates = $this->geocoder->geocode($formattedAddress)) {
                $longitude = $coordinates['lng'];
                $latitude = $coordinates['lat'];
            }

        } catch (\Exception $exception) {

            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_BAD_REQUEST
            );

        }

        return new JsonResponse(
            [
                'success' => true,
                'latitude' => $latitude,
                'longitude' => $longitude
            ], Response::HTTP_OK
        );


    }
}
