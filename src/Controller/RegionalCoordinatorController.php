<?php

namespace App\Controller;

use App\Entity\RegionalCoordinator;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RegionalCoordinatorController
 * @package App\Controller
 * @Route("/dashboard")
 */
class RegionalCoordinatorController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @Security("is_granted('ROLE_REGIONAL_COORDINATOR_USER')")
     *
     * @Route("/reporting", name="dashboard_reports")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function reportAction(Request $request) {

        /** @var RegionalCoordinator $user */
        $user = $this->getUser();

        if(!$request->query->has('type')) {
            throw new \Exception("A report type must be provided.");
        }


        switch ($request->query->get('type')) {
            case 'professionals':
                $data = $this->professionalUserRepository->fetchAll();
                break;
            case 'educators':
                $data = $this->educatorUserRepository->fetchAll();
                break;
            case 'school_administrators':
                $data = $this->schoolAdministratorRepository->fetchAll();
                break;
            default:
                throw new \Exception("Invalid report type.");
                break;
        }

        $response = new Response($this->serializer->encode($data, 'csv'));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$this->generateRandomString()}.csv");

        return $response;
    }
}
