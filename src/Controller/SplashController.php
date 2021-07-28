<?php

namespace App\Controller;

use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\TeachLessonExperience;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Mailer\SecurityMailer;
use App\Model\ForgotPassword;
use App\Model\ResetPassword;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use App\Util\ServiceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
