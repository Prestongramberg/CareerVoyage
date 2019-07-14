<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\ProfessionalUser;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
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
        for($i = 1; $i <= 20; $i++) {
            $company = new Company();
            $company->setName($this->faker->randomElement(self::$companyNames));
            $company->setAddress($this->faker->randomElement(self::$companyAddresses));
            $company->setShortDescription($this->faker->randomElement(self::$companyDescriptions));
            $company->setWebsite($this->faker->randomElement(self::$companyWebsites));
            $company->setEmailAddress($this->faker->randomElement(self::$companyEmails));

            if($i >= 1 && $i <= 10) {
                $company->setPrimaryIndustry($this->getReference("industry{$i}"));
            }

            $manager->persist($company);
        }
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
}
