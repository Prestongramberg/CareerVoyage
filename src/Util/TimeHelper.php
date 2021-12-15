<?php

namespace App\Util;

trait TimeHelper
{
    /**
     * @param int    $lower
     * @param int    $upper
     * @param int    $step
     * @param string $format
     *
     * @return array
     * @throws \Exception
     * @see https://stackoverflow.com/questions/3903317/how-can-i-make-an-array-of-times-with-half-hour-intervals
     */
    public function hoursRange(int $lower = 0, int $upper = 86400, int $step = 3600, string $format = '' ) {
        $times = array();

        if ( empty( $format ) ) {
            $format = 'g:i a';
        }

        foreach ( range( $lower, $upper, $step ) as $increment ) {
            $increment = gmdate( 'H:i', $increment );

            list( $hour, $minutes ) = explode( ':', $increment );

            $date = new \DateTime( $hour . ':' . $minutes );

            $times[$date->format( $format )] = (string) $increment;
        }

        return $times;
    }
}