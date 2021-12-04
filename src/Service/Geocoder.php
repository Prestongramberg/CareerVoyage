<?php
/**
 * Created by PhpStorm.
 * User: joshcrawmer
 * Date: 2019-11-20
 * Time: 00:59
 */

namespace App\Service;

use App\Repository\StateRepository;
use SKAgarwal\GoogleApi\PlacesApi;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Geocoder
{
    /**
     * @var StateRepository
     */
    private $stateRepository;

    /**
     * @var string
     */
    private $googleApiKey;

    /**
     * @param StateRepository $stateRepository
     * @param string          $googleApiKey
     */
    public function __construct(StateRepository $stateRepository, string $googleApiKey)
    {
        $this->stateRepository = $stateRepository;
        $this->googleApiKey    = $googleApiKey;
    }

    /**
     * @param $address string
     *
     * @return bool|array
     */
    public function geocode($address)
    {
        // Get JSON results from this request
        $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $this->googleApiKey);
        $geo = json_decode($geo, true); // Convert the JSON to an array
        if (isset($geo['status']) && ($geo['status'] == 'OK')) {
            if (!empty($geo['results'][0]['geometry']['location']['lat']) && !empty($geo['results'][0]['geometry']['location']['lng'])) {
                return $geo['results'][0]['geometry']['location'];
            }
        }

        return false;
    }

    /**
     * Calculates the max search square lat/lng points from the starting point
     * of a provided latitude and longitude for a give distance (radius) distance defaults to 70
     *
     * @param     $latitude
     * @param     $longitude
     * @param int $distance
     *
     * @return array
     */
    public function calculateSearchSquare($latitude, $longitude, $distance = 70)
    {

        $latN = rad2deg(asin(sin(deg2rad($latitude)) * cos($distance / 3959) + cos(deg2rad($latitude)) * sin($distance / 3959) * cos(deg2rad(0))));

        $latS = rad2deg(asin(sin(deg2rad($latitude)) * cos($distance / 3959) + cos(deg2rad($latitude)) * sin($distance / 3959) * cos(deg2rad(180))));

        $lonE = rad2deg(deg2rad($longitude) + atan2(sin(deg2rad(90)) * sin($distance / 3959) * cos(deg2rad($latitude)), cos($distance / 3959) - sin(deg2rad($latitude)) * sin(deg2rad($latN))));

        $lonW = rad2deg(deg2rad($longitude) + atan2(sin(deg2rad(270)) * sin($distance / 3959) * cos(deg2rad($latitude)), cos($distance / 3959) - sin(deg2rad($latitude)) * sin(deg2rad($latN))));

        return [$latN, $latS, $lonE, $lonW];
    }

    /**
     * @param $addressSearch
     *
     * @return array
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     * @throws \Exception
     */
    public function getAddressComponentsFromSearchString($addressSearch)
    {

        /**
         * @see https://github.com/SachinAgarwal1337/google-places-api#place-search
         */
        $googlePlaces = new PlacesApi('AIzaSyBsyd95RCwjpoNBiAsI4BQF4oYwkfC8EvQ');
        $street       = null;
        $postalCode   = null;
        $city         = null;
        $state        = null;

        $response = $googlePlaces->placeAutocomplete($addressSearch, [
            'components' => 'country:us',
            'types'      => 'address',
        ]);

        $response    = $response->toArray();
        $predictions = $response['predictions'] ?? [];

        if (empty($predictions[0]['place_id'])) {
            throw new \Exception("No address found for search string");
        }

        $placeId           = $predictions[0]['place_id'];
        $response          = $googlePlaces->placeDetails($placeId);
        $response          = $response->toArray();
        $addressComponents = $response['result']['address_components'];

        foreach ($addressComponents as $addressComponent) {
            $componentType = $addressComponent['types'][0];

            switch ($componentType) {
                case 'street_number':
                    $street = $addressComponent['long_name'];
                    break;
                case 'route':
                    $street .= ' ' . $addressComponent['short_name'];
                    break;
                case 'locality':
                    $city = $addressComponent['long_name'];
                    break;
                case 'administrative_area_level_1':
                    $state = $addressComponent['long_name'];
                    break;
                case 'postal_code':
                    $postalCode = $addressComponent['long_name'];
                    break;
            }
        }

        $state = $this->stateRepository->findOneBy([
            'name' => $state,
        ]);

        return [
            'street'     => $street,
            'city'       => $city,
            'state'      => $state,
            'postalCode' => $postalCode,
        ];

    }
}