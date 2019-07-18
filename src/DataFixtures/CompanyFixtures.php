<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CompanyFixtures extends BaseFixture
{

    private static $companyNames = [
        '3M',
        'Walmart',
        'Target',
    ];
    private static $companyAddresses = [
        '2324 85th ave Baldwin, WI 54002',
        '144 west 3rd street New Richmond, WI 54017',
        '111 9th street east Hammond, WI 55125',
    ];
    private static $companyDescriptions = [
        'Here at our company we pride ourselves in the best products ever made.',
        'We have the best customer service ever here. ',
        'this is another short company description',
    ];

    private static $companyWebsites = [
        'http://www.3m.com',
        'http://www.walmart.com',
        'http://www.target.com',
    ];

    private static $companyEmails = [
        'josh@3m.com',
        'tom@walmart.com',
        'jess@target.com',
    ];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var string
     */
    private $uploadsPath;

    /**
     * CompanyFixtures constructor.
     * @param EntityManagerInterface $entityManager
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param $uploadsPath
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        $uploadsPath
    ) {
        $this->entityManager = $entityManager;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->uploadsPath = $uploadsPath;
    }

    protected function loadData(ObjectManager $manager)
    {
            $company = new Company();
            $company->setName('Best Buy');
            $company->setAddress('7601 Penn Ave. S Richfield, MN 55423');
            $company->setShortDescription('Best Buy Co., Inc. is an American multinational consumer electronics retailer headquartered in Richfield, Minnesota. It was originally founded by Richard M. Schulze and James Wheeler in 1966 as an audio specialty store called Sound of Music');
            $company->setWebsite('http://www.bestbuy.com');
            $company->setEmailAddress('info@bestbuy.com');

            $thumbnailImage = new File(__DIR__.'/images/bestbuy.jpg');

            if($thumbnailImage) {
                $mimeType = $thumbnailImage->getMimeType();
                $newFilename = $this->fakeUploadImage('bestbuy.jpg', UploaderHelper::THUMBNAIL_IMAGE);
                $image = new Image();
                $image->setOriginalName($thumbnailImage->getFilename() ?? $newFilename);
                $image->setMimeType($mimeType ?? 'application/octet-stream');
                $image->setFileName($newFilename);
                $company->setThumbnailImage($image);
                $manager->persist($image);

                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::THUMBNAIL_IMAGE) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            $company->setPrimaryIndustry($this->getReference("industry1"));
            $manager->persist($company);

            $company = new Company();
            $company->setName('Walmart');
            $company->setAddress('1101 East street Bentonville, Arkansas 67009');
            $company->setShortDescription('Walmart Inc. is an American multinational retail corporation that operates a chain of hypermarkets, discount department stores, and grocery stores, headquartered in Bentonville, Arkansas.');
            $company->setWebsite('http://www.walmart.com');
            $company->setEmailAddress('info@walmart.com');

            $thumbnailImage = new File(__DIR__.'/images/walmart.jpg');


            if($thumbnailImage) {
                $mimeType = $thumbnailImage->getMimeType();
                $newFilename = $this->fakeUploadImage('walmart.jpg', UploaderHelper::THUMBNAIL_IMAGE);
                $image = new Image();
                $image->setOriginalName($thumbnailImage->getFilename() ?? $newFilename);
                $image->setMimeType($mimeType ?? 'application/octet-stream');
                $image->setFileName($newFilename);
                $company->setThumbnailImage($image);
                $manager->persist($image);

                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::THUMBNAIL_IMAGE) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            $company->setPrimaryIndustry($this->getReference("industry2"));
            $manager->persist($company);

            $company = new Company();
            $company->setName('Target');
            $company->setAddress('8890 west 9th street Miami FL 65430');
            $company->setShortDescription('Target Corporation is the eighth-largest retailer in the United States, and is a component of the S&P 500 Index');
            $company->setWebsite('http://www.target.com');
            $company->setEmailAddress('info@target.com');

            $thumbnailImage = new File(__DIR__.'/images/target.jpg');

            if($thumbnailImage) {
                $mimeType = $thumbnailImage->getMimeType();
                $newFilename = $this->fakeUploadImage('target.jpg', UploaderHelper::THUMBNAIL_IMAGE);
                $image = new Image();
                $image->setOriginalName($thumbnailImage->getFilename() ?? $newFilename);
                $image->setMimeType($mimeType ?? 'application/octet-stream');
                $image->setFileName($newFilename);
                $company->setThumbnailImage($image);
                $manager->persist($image);

                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::THUMBNAIL_IMAGE) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            $company->setPrimaryIndustry($this->getReference("industry13"));
            $manager->persist($company);

            $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            IndustryFixtures::class,
        );
    }

    /**
     * @param $imageName
     * @param $folder
     * @return string
     */
    private function fakeUploadImage($imageName, $folder): string
    {
        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$imageName;
        $fs->copy(__DIR__.'/images/'.$imageName, $targetPath, true);
        return $this->uploaderHelper->upload(new File($targetPath), $folder);
    }
}
