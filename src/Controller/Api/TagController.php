<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Entity\User;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagController
 *
 * @package App\Controller
 * @Route("/api/tags")
 */
class TagController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/search", name="api_tag_search", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $limitPerPage = 10;
        $searchTerm = $request->query->get('value');

        $filterBuilder = $this->tagRepository->createQueryBuilder('t')
                                                ->andWhere('t.systemDefined = :systemDefined')
                                                ->setParameter('systemDefined', true)
                                                ->addOrderBy('t.name', 'ASC');

        if($searchTerm) {
            $filterBuilder->andWhere('t.name LIKE :searchTerm')
                          ->setParameter('searchTerm', '%'.$searchTerm.'%');
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate($filterQuery, $request->query->getInt('page', 1), $limitPerPage);

        $items   = $pagination->getItems();
        $hasMore = ($pagination->getCurrentPageNumber() * $limitPerPage) < $pagination->getTotalItemCount();

        $results = [];
        /** @var Tag $item */
        foreach ($items as $item) {
            $results[] = [
                'value'   => $item->getName(),
                'id' => $item->getId()
            ];
        }

        return new JsonResponse([
            'results'    => $results,
            'pagination' => [
                'more' => $hasMore,
            ],
        ]);
    }
}
