<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;

class UploaderHelper
{
    const PROFILE_PHOTO    = 'profile_photo';
    const COMPANY_LOGO     = 'company_logo';
    const COMPANY_IMAGE    = 'company_image';
    const COMPANY_DOCUMENT = 'company_document';
    const HERO_IMAGE       = 'hero_image';
    const THUMBNAIL_IMAGE  = 'thumbnail_image';
    const FEATURE_IMAGE    = 'featured_image';
    const COMPANY_PHOTO    = 'company_photo';
    const RESOURCE         = 'resource';
    const COMPANY_RESOURCE = 'company_resource';
    const SCHOOL_RESOURCE  = 'school_resource';
    const LESSON_THUMBNAIL = 'lesson_thumbnail';
    const LESSON_FEATURED  = 'lesson_featured';
    const EXPERIENCE_FILE  = 'experience_file';
    const LESSON_RESOURCE  = 'lesson_resource';
    const SCHOOL_PHOTO     = 'school_photo';
    const STUDENT_IMPORT   = 'student_import';
    const EDUCATOR_IMPORT  = 'educator_import';


    private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    public function upload(File $file, $folder = self::PROFILE_PHOTO)
    {

        $destination = $this->uploadsPath . '/' . $folder;

        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadStudentImport(UploadedFile $uploadedFile): string
    {
        $destination      = $this->uploadsPath . '/' . self::STUDENT_IMPORT;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename      = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadEducatorImport(UploadedFile $uploadedFile): string
    {
        $destination      = $this->uploadsPath . '/' . self::EDUCATOR_IMPORT;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename      = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadProfilePhoto(UploadedFile $uploadedFile): string
    {
        $destination      = $this->uploadsPath . '/' . self::PROFILE_PHOTO;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename      = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadCompanyLogo(UploadedFile $uploadedFile): string
    {
        $destination      = $this->uploadsPath . '/' . self::COMPANY_LOGO;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename      = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadHeroImage(UploadedFile $uploadedFile): string
    {
        $destination      = $this->uploadsPath . '/' . self::HERO_IMAGE;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename      = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadCompanyImage(UploadedFile $uploadedFile): string
    {
        $destination      = $this->uploadsPath . '/' . self::COMPANY_IMAGE;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename      = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function uploadCompanyDocument(UploadedFile $uploadedFile): string
    {
        $destination      = $this->uploadsPath . '/' . self::COMPANY_DOCUMENT;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename      = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
        return 'uploads/' . $path;
    }

    public function getUploadsPath()
    {
        return $this->uploadsPath;
    }

    /**
     * For security reasons symfony uses the following method to determine file extension
     * https://www.tutorialfor.com/questions-41236.htm
     * This can cause us issues on determining extension and mime type for CSV file
     *
     * @param File $file
     *
     * @return string|null
     */
    public function guessExtension(File $file)
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        // For security reasons symfony uses the following method to determine file extension
        // https://www.tutorialfor.com/questions-41236.htm
        // This can cause issues guessing whether or not it's a csv file
        if (pathinfo(basename($originalFilename)) ['extension'] === 'csv') {
            $extension = 'csv';
        } else {
            $extension = $file->getClientOriginalExtension();
        }

        return $extension;
    }

}