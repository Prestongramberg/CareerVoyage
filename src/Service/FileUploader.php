<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Image;
use SpacesConnect;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $params;

    private $accessKey;

    private $secretKey;

    private $name;

    private $region;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;

        $this->accessKey = $this->params->get('space_access_key');
        $this->secretKey = $this->params->get('space_secret_key');
        $this->name = $this->params->get('space_name');
        $this->region = $this->params->get('space_region');
    }

    public function upload(Image $image)
    {
        try {
            $space = new SpacesConnect($this->accessKey, $this->secretKey, $this->name, $this->region);
            $space->UploadFile($image->getFile()->getRealPath(), "public", $image->getNewName(), $image->getFile()->getMimeType());
            return $space->GetObject($image->getNewName());
        } catch (\SpacesAPIException $e) {
            return false;
        }
    }
}