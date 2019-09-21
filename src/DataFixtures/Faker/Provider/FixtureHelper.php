<?php

namespace App\DataFixtures\Faker\Provider;


use App\Entity\CompanyPhoto;
use App\Entity\Experience;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\Site;
use App\Entity\State;
use App\Repository\SiteRepository;
use App\Repository\StateRepository;
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
     * @var StateRepository
     */
    private $stateRepository;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * FixtureHelper constructor.
     * @param UploaderHelper $uploaderHelper
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param EntityManagerInterface $entityManager
     * @param StateRepository $stateRepository
     * @param SiteRepository $siteRepository
     */
    public function __construct(
        UploaderHelper $uploaderHelper,
        ImageCacheGenerator $imageCacheGenerator,
        EntityManagerInterface $entityManager,
        StateRepository $stateRepository,
        SiteRepository $siteRepository
    ) {
        $this->uploaderHelper = $uploaderHelper;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->entityManager = $entityManager;
        $this->stateRepository = $stateRepository;
        $this->siteRepository = $siteRepository;
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

    /**
     * 24 maps to minnesota
     * @param int $stateId
     * @return State|object|null
     */
    public function state($stateId = 24) {
        return $this->entityManager->getRepository(State::class)->find($stateId);
    }

    /**
     * @param int $siteId
     * @return State|object|null
     */
    public function site($siteId = 2) {
        return $this->entityManager->getRepository(Site::class)->find($siteId);
    }

    /**
     * 1 maps to Southeast region
     * @param int $regionId
     * @return State|object|null
     */
    public function region($regionId = 1) {
        return $this->entityManager->getRepository(Region::class)->find($regionId);
    }

    /**
     * 1 maps to Southeast region
     * @param int $regionId
     * @return State|object|null
     */
    public function schools($regionId = 1) {
        return $this->entityManager->getRepository(School::class)->findBy([
            'region' => $regionId
        ], null, 2);
    }

    /**
     * 1 school result
     * @param int $regionId
     * @return State|object|null
     */
    public function school($regionId = 1) {
        return $this->entityManager->getRepository(School::class)->findOneBy([
            'region' => $regionId
        ]);
    }

    /**
     * 1 school result
     * @param int $schoolId
     * @return State|object|null
     */
    public function schoolById($schoolId = 1) {
        return $this->entityManager->getRepository(School::class)->find($schoolId);
    }

    public function randomSchool() {
        $schools = $this->entityManager->getRepository(School::class)->findAll();
        $schoolId = $schools[rand(1, count($schools) - 1)]->getId();
        return $this->entityManager->getRepository(School::class)->find($schoolId);
    }

    public function grade($gradeId) {
        return $this->entityManager->getRepository(Grade::class)->find($gradeId);
    }

    public function randomExperienceType() {
        return Experience::$types[array_rand(Experience::$types)];
    }

    public function randomPaymentType() {
        return Experience::$paymentTypes[array_rand(Experience::$paymentTypes)];
    }
}