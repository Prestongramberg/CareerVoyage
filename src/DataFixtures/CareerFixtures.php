<?php

namespace App\DataFixtures;

use App\Entity\Career;
use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CareerFixtures extends Fixture
{
    private static $careerTitles = [
        'Agriculture, Food & Natural Resources',
        'Architecture & Construction',
        'Arts, A/V Technology & Communications',
        'Business Management & Administration',
        'Education & Training',
        'Finance',
        'Government & Public Administration',
        'Health Science',
        'Hospitality & Tourism',
        'Human Services',
        'Information Technology',
        'Law, Public Safety, Corrections & Security',
        'Manufacturing',
        'Marketing',
        'Science, Technology, Engineering & Mathematics',
        'Transportation, Distribution & Logistics',
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

    public function load(ObjectManager $manager)
    {
        $i = 1;
        foreach(self::$careerTitles as $careerTitle) {
            $career = new Career();
            $career->setTitle($careerTitle);
            $manager->persist($career);
            /*$this->addReference("industry{$i}", $industry);*/
            $i++;
        }
        $manager->flush();
    }
}
