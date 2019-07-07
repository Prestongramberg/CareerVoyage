<?php

namespace App\Service;

use App\Entity\Image;
use Gedmo\Sluggable\Util\Urlizer;
use SpacesConnect;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * @var string
     */
    private $uploadsPath;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var mixed
     */
    private $accessKey;

    /**
     * @var mixed
     */
    private $secretKey;

    /**
     * @var mixed
     */
    private $name;

    /**
     * @var mixed
     */
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

    /**
     * @param UploadedFile $uploadedFile
     * @param $uploadPath
     * @return bool
     */
    public function uploadPhoto(UploadedFile $uploadedFile, $uploadPath)
    {
        try {
            $space = new SpacesConnect($this->accessKey, $this->secretKey, $this->name, $this->region);
            $space->UploadFile(
                $uploadedFile->getRealPath(),
                "public",
                $uploadPath,
                $uploadedFile->getMimeType()
            );

            return $space->GetObject($uploadPath)['@metadata']['effectiveUri'];
        } catch (\SpacesAPIException $e) {
            return false;
        }
    }

    public function getPhoto($uploadPath) {
        $space = new SpacesConnect($this->accessKey, $this->secretKey, $this->name, $this->region);

        try {
            return $space->GetObject($uploadPath)['@metadata']['effectiveUri'];
        } catch (\SpacesAPIException $e) {
            return false;
        }
    }

    public function photoExists($uploadPath) {
        $space = new SpacesConnect($this->accessKey, $this->secretKey, $this->name, $this->region);

        try {
            $photo = $space->GetObject($uploadPath);
            return true;
        } catch (\SpacesAPIException $e) {
            return false;
        }
    }

}