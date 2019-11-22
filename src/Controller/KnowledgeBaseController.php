<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KnowledgeBaseController
 * @package App\Controller
 * @Route("/dashboard")
 */
class KnowledgeBaseController extends AbstractController
{

	/**
	 * @Route("/knowledge-base", name="knowledge-base", methods={"GET"})
	 * @param Request $request
	 * @param SessionInterface $session
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function indexAction(Request $request, SessionInterface $session) {

		/** @var User $user */
		$user = $this->getUser();

		return $this->render('knowledgebase/index.html.twig', [
			'user' => $user
		]);
	}
}
