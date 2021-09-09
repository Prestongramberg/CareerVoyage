<?php

namespace App\Controller\Api;

use App\Entity\Image;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoFavorite;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ImageController
 * @package App\Controller
 * @Route("/api")
 */
class ImageController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/images/upload", name="api_images_upload", options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function imageUploadAction(Request $request)
    {

        $user = $this->getUser();

        $folder = $request->query->get('folder', null);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile= $request->files->get('file');

        if ($uploadedFile && $folder) {
            $mimeType    = $uploadedFile->getMimeType();
            $newFilename = $this->uploaderHelper->upload($uploadedFile, $folder);
            $image       = new Image();
            $image->setOriginalName($uploadedFile->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath($folder) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/' . $folder . '/' . $newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }
}