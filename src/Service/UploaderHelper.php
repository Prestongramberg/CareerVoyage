<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;

class UploaderHelper
{
    const PROFILE_PHOTO = 'profile_photo';
    const COMPANY_LOGO = 'company_logo';
    const COMPANY_IMAGE = 'company_image';
    const COMPANY_DOCUMENT = 'company_document';
    const HERO_IMAGE = 'hero_image';
    const THUMBNAIL_IMAGE = 'thumbnail_image';
    const FEATURE_IMAGE = 'feature_image';
    const COMPANY_PHOTO = 'company_photo';
    const COMPANY_RESOURCE = 'company_resource';
    const LESSON_THUMBNAIL = 'lesson_thumbnail';
    const LESSON_FEATURED = 'lesson_featured';
    const EXPERIENCE_WAVER = 'experience_waver';
    const EXPERIENCE_OTHER_FILE = 'experience_other_file';


    private $uploadsPath;
    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    public function upload(File $file, $folder = self::PROFILE_PHOTO) {

        $destination = $this->uploadsPath.'/' . $folder;

        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->guessExtension();

        $file->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadProfilePhoto(UploadedFile $uploadedFile): string
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

    public function uploadHeroImage(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath.'/' . self::HERO_IMAGE;
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

    public function uploadCompanyDocument(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath.'/' . self::COMPANY_DOCUMENT;
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

    public function getUploadsPath() {
        return $this->uploadsPath;
    }
}