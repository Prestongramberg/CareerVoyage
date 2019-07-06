<?php

namespace App\Service;

use App\Entity\Image;
use Gedmo\Sluggable\Util\Urlizer;
use SpacesConnect;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $uploadsPath;

    private $params;

    private $accessKey;

    private $secretKey;

    private $name;

    private $region;

    public function __construct(ParameterBagInterface $params, string $uploadsPath)
    {
        $this->params = $params;
        $this->uploadsPath = $uploadsPath;
        $this->accessKey = $this->params->get('space_access_key');
        $this->secretKey = $this->params->get('space_secret_key');
        $this->name = $this->params->get('space_name');
        $this->region = $this->params->get('space_region');
    }

/*    public function upload(UploadedFile $uploadedFile): string {

        $destination = $this->getParameter('kernel.project_dir').'/public/uploads/article_image';
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;

    }*/

    /**
     * @param UploadedFile $uploadedFile
     * @param $newFileName
     * @return bool
     */
    public function uploadPhoto(UploadedFile $uploadedFile, $newFileName)
    {
        $filePath = $this->uploadsPath . '/' . $newFileName;
        try {
            $space = new SpacesConnect($this->accessKey, $this->secretKey, $this->name, $this->region);
            $space->UploadFile(
                $uploadedFile->getRealPath(),
                "public",
                $filePath,
                $uploadedFile->getMimeType()
            );

            return $space->GetObject($filePath)['@metadata']['effectiveUri'];
        } catch (\SpacesAPIException $e) {
            return false;
        }
    }
}