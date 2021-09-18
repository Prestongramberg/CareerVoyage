<?php

namespace App\Controller\Api;

use App\Entity\CompanyPhoto;
use App\Entity\Image;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoFavorite;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
 * @Route("/api/images")
 */
class ImageController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/upload", name="api_images_upload", options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function imageUploadAction(Request $request)
    {
        $user = $this->getUser();
        $folder = $request->query->get('folder', null);
        $image       = new Image();

        if ($request->query->has('companyId')) {
            $companyId = $request->query->get('companyId');
            $company   = $this->companyRepository->find($companyId);
            $image       = new CompanyPhoto();
            $image->setCompany($company);
            $folder = UploaderHelper::COMPANY_PHOTO;
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile= $request->files->get('file');

        if ($uploadedFile && $folder) {
            $mimeType    = $uploadedFile->getMimeType();
            $newFilename = $this->uploaderHelper->upload($uploadedFile, $folder);
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
                    'unCroppedUrl' => '/uploads/' . $folder . '/' . $newFilename,
                    'deleteUrl' => $this->generateUrl('api_images_delete', ['id' => $image->getId()]),
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

    /**
     * @Route("/{id}/delete", name="api_images_delete", options = {"expose" = true })
     * @Method({"GET", "POST"})
     * @param Request $request
     *
     * @param Image   $image
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Image $image)
    {
        /** @var User $user */
        $user = $this->getUser();

        $imageId = $image->getId();

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'id' => $imageId,

            ], Response::HTTP_OK
        );
    }
}