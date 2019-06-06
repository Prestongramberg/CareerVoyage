<?php

namespace App\Controller\Api;

use App\Form\MusicImportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BingoController
 * @package App\Controller\Api
 * @Route("bingo")
 */
class BingoController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * BingoController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/music-import", name="music_import", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function importScoresAction(Request $request) {

        $form = $this->createForm(MusicImportType::class);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'api/form/music_import_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        if ($form->isSubmitted() && !$form->isValid()) {

            if(!$form->isValid()) {
                return new JsonResponse(
                    [
                        'success' => false,
                        'formMarkup' => $formMarkup,
                    ], Response::HTTP_BAD_REQUEST
                );
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // perform the score import
            $file = $form['musicImport']->getData();
            $tempPathName = $file->getRealPath();

            $rowNo = 1;
            $columns = [];
            if (($fp = fopen($tempPathName, "r")) !== FALSE) {
                while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {

                    if($rowNo === 1) {
                        $columns = $row;
                        $rowNo++;
                        continue;
                    }

                    $data = array_combine($columns, $row);

                    // making sure the string columns that need to be stored
                    // as an integer are converted
                    foreach($data as $key => $value) {
                        switch ($key) {
                            case 'Hosts ID':
                            case 'Location ID':
                            case 'Score':
                            case 'Session':
                            case 'Team ID':
                                $data[$key] = (int) $value;
                                break;
                        }
                    }

                    // converting array structure to our Score entity
                    $score = $this->denormalizer->denormalize(
                        $data,
                        Score::class
                    );

                    // save to the database
                    $this->entityManager->persist($score);
                    $this->entityManager->flush();
                    $rowNo++;

                }
                fclose($fp);
            }
        }

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup,
            ],
            Response::HTTP_OK
        );
    }
}