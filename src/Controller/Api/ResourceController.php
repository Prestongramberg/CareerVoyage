<?php

namespace App\Controller\Api;

use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResourceController
 * @package App\Controller
 * @Route("/api/resources")
 */
class ResourceController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

}