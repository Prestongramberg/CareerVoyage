<?php
/**
 * Created by PhpStorm.
 * User: joshcrawmer
 * Date: 2019-11-20
 * Time: 00:59
 */

namespace App\Service;

class Geocoder
{
    /**
     * @var string
     */
    private $googleApiKey;

    /**
     * Geocoder constructor.
     * @param string $googleApiKey
     */
    public function __construct(string $googleApiKey)
    {
        $this->googleApiKey = $googleApiKey;
    }

    /**
     * @param $address string
     * @return bool|array
     */
    public function geocode($address) {
        // Get JSON results from this request
        $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false&key='.$this->googleApiKey);
        $geo = json_decode($geo, true); // Convert the JSON to an array
        if (isset($geo['status']) && ($geo['status'] == 'OK')) {
            if(!empty($geo['results'][0]['geometry']['location']['lat']) && !empty($geo['results'][0]['geometry']['location']['lng'])) {
                return $geo['results'][0]['geometry']['location'];
            }
        }
        return false;
    }

    /**
     * Calculates the max search square lat/lng points from the starting point
     * of a provided latitude and longitude for a give distance (radius) distance defaults to 70
     *
     * @param $latitude
     * @param $longitude
     * @param int $distance
     * @return array
     */
    public function calculateSearchSquare($latitude, $longitude, $distance = 70) {

        $latN = rad2deg(asin(sin(deg2rad($latitude)) * cos($distance / 3959)
            + cos(deg2rad($latitude)) * sin($distance / 3959) * cos(deg2rad(0))));

        $latS = rad2deg(asin(sin(deg2rad($latitude)) * cos($distance / 3959)
            + cos(deg2rad($latitude)) * sin($distance / 3959) * cos(deg2rad(180))));

        $lonE = rad2deg(deg2rad($longitude) + atan2(sin(deg2rad(90))
                * sin($distance / 3959) * cos(deg2rad($latitude)), cos($distance / 3959)
                - sin(deg2rad($latitude)) * sin(deg2rad($latN))));

        $lonW = rad2deg(deg2rad($longitude) + atan2(sin(deg2rad(270))
                * sin($distance / 3959) * cos(deg2rad($latitude)), cos($distance / 3959)
                - sin(deg2rad($latitude)) * sin(deg2rad($latN))));

        return [$latN, $latS, $lonE, $lonW];
    }
}