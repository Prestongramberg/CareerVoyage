<?php

namespace App\DataFixtures\Faker\Provider;


use App\Entity\CompanyPhoto;
use App\Entity\Industry;
use App\Entity\SecondaryIndustry;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Faker\Provider\Base;
use App\Entity\Image;

class FixtureHelper
{

    /**
     * @var UploaderHelper $uploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * UploadProvider constructor.
     * @param UploaderHelper $uploaderHelper
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        UploaderHelper $uploaderHelper,
        ImageCacheGenerator $imageCacheGenerator,
        EntityManagerInterface $entityManager
    ) {
        $this->uploaderHelper = $uploaderHelper;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->entityManager = $entityManager;
    }


    public function upload($folder, $imageCategory, $width = '400', $height = '400')
    {
        $faker = \Faker\Factory::create();
        $path = $faker->image(sys_get_temp_dir(), $width, $height, $imageCategory);
        $newFileName = $this->uploaderHelper->upload(new File($path), $folder);
        $path = $this->uploaderHelper->getPublicPath($folder) .'/'. $newFileName;
        $this->imageCacheGenerator->cacheImageForAllFilters($path);
        return $newFileName;
    }

    public function imageObject($folder, $imageCategory, $width = '640', $height = '480') {

        $faker = \Faker\Factory::create();
        $path = $faker->image(sys_get_temp_dir(), $width, $height, $imageCategory);
        $file = new File($path);
        $mimeType = $file->getMimeType();
        $newFileName = $this->uploaderHelper->upload($file, $folder);

        $path = $this->uploaderHelper->getPublicPath($folder) .'/'. $newFileName;
        $this->imageCacheGenerator->cacheImageForAllFilters($path);

        $image = new Image();
        $image->setOriginalName($file->getFilename() ?? $newFileName);
        $image->setMimeType($mimeType ?? 'application/octet-stream');
        $image->setFileName($newFileName);
        return $image;
    }

    public function primaryIndustry($primaryIndustryId) {
        return $this->entityManager->getRepository(Industry::class)->find($primaryIndustryId);
    }

    public function secondaryIndustry($primaryIndustryId) {
        return $this->entityManager->getRepository(SecondaryIndustry::class)->findBy([
            'primaryIndustry' => $primaryIndustryId
        ], null, 5);
    }

}