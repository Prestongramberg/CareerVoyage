<?php

namespace App\Controller;

use App\Entity\KnowledgeResource;
use App\Entity\Resource;
use App\Entity\User;
use App\Repository\ResourceRepository;
use App\Util\ServiceHelper;
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

    use ServiceHelper;

    /**
     * @Route("/knowledge-base", name="knowledge-base", methods={"GET"})
     * @param Request          $request
     * @param SessionInterface $session
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
	public function indexAction(Request $request, SessionInterface $session) {

		/** @var User $user */
		$user = $this->getUser();

		$resources = $this->knowledgeResourceRepository->findAll();

		$resourceArray = [];
		/** @var Resource $resource */
        foreach($resources as $resource) {
		    $resourceArray[$resource->getTab()][$resource->getTitle()][] = [
		      'description' => $resource->getDescription(),
              'url' => $resource->getUrl(),
              'id' => $resource->getId()
            ];
        }

		return $this->render('knowledgebase/index.html.twig', [
			'user' => $user,
            'resourceArray' => $resourceArray,
		]);
	}

    /**
     * @Route("/knowledge-base/add-resource", name="knowledge_base_add_resource", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addResourceAction(Request $request, SessionInterface $session) {

        /** @var User $user */
        $user = $this->getUser();
        $title = $request->request->get('resource_title', null);
        $url = $request->request->get('resource_url', null);
        $description = $request->request->get('resource_description', null);
        $tab = $request->request->get('resource_tab', null);

        $resource = new KnowledgeResource();
        $resource->setTitle($title);
        $resource->setUrl($url);
        $resource->setDescription($description);
        $resource->setTab($tab);

        $this->entityManager->persist($resource);
        $this->entityManager->flush();


        $this->addFlash('success', 'Resource successfully added');

        return $this->redirectToRoute('knowledge-base');
    }

    /**
     * @Route("/knowledge-base/{id}/delete-resource", name="knowledge_base_delete_resource", methods={"GET"})
     * @param Request  $request
     * @param Resource $resource
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteResourceAction(Request $request, Resource $resource) {

        /** @var User $user */
        $user = $this->getUser();

        $this->entityManager->remove($resource);
        $this->entityManager->flush();

        $this->addFlash('success', 'Resource successfully removed');

        return $this->redirectToRoute('knowledge-base');
    }
}
