<?php

namespace App\Controller;

use App\Entity\User;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SplashController
 * @package App\Controller
 */
class SplashController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/splash/{splash}", name="splash_index", methods={"GET"})
     * @param Request $request
     * @param         $splash
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, $splash)
    {
        /** @var User $user */
        $user = $this->getUser();

        switch ($splash) {

            case 'professional-welcome':

                return $this->render('splash/professional/welcome.html.twig', [
                    'user' => $user
                ]);

                break;

            case 'educator-welcome':

                return $this->render('splash/educator/welcome.html.twig', [
                    'user' => $user
                ]);

                break;
            default:

                return $this->redirectToRoute('welcome');

                break;
        }

    }
}
