<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;

class UploaderHelper
{
    const PROFILE_PHOTO = 'profile_photo';
    const COMPANY_LOGO = 'company_logo';
    const COMPANY_IMAGE = 'company_image';

    private $uploadsPath;
    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    public function uploadArticleImage(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath.'/' . self::PROFILE_PHOTO;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadCompanyLogo(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath.'/' . self::COMPANY_LOGO;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadCompanyImage(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath.'/' . self::COMPANY_IMAGE;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
        return 'uploads/'.$path;
    }

}