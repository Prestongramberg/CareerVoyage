<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class YoutubeVideoId extends Constraint
{
    public $message = 'Please enter a valid Youtube Video ID';

}