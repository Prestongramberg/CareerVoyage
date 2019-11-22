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
}