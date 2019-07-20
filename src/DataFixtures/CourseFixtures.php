<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CourseFixtures extends Fixture
{
    private static $courseTitles = [
        'AP Comparative Politics',
        'AP US Government',
        'Accounting',
        'Agri- Business',
        'Architecture',
        'Art & Design',
        'Automation and Robotics',
        'Biology',
        'Business',
        'Career Exploration',
        'Chemistry',
        'Child Development',
        'Civics',
        'Computer Science',
        'Construction',
        'Culinary Skills',
        'Cyber Security',
        'Design Thinking',
        'Economics',
        'Energy, Environment and Society',
        'Engineering High School Level',
        'English: Communications',
        'Entrepreneurship',
        'Environmental Science',
        'Fashion Design & Apparel',
        'Food Science',
        'Geography',
        'Graphic Design',
        'Health Career Exploration',
        'Healthcare',
        'Human Geography',
        'Information Technology',
        'Interior Design',
        'Intro to Engineering: Grades 5-9',
        'Intro to Transportation Industry',
        'Knitting, Crocheting and other crafts',
        'Manufacturing',
        'Marketing',
        'Math',
        'Media Studies',
        'Metals Technology',
        'Metals: construction, installation and operation',
        'OSHA 10 Training',
        'Photography',
        'Power & Energy: Engines',
        'Project Management',
        'Psychology',
        'Race 2 Reduce Grade 4',
        'Renewable Energy Solar Focus',
        'Science',
        'Senior Strategies: post high school lifeskill',
        'Service Projects',
        'Sociology',
        'Statistics',
        'TV & Video Production',
        'Transition skills for the post high school world',
        'Web Design',
        'Wood Technology & Carpentry',
        'Workplace Skills',
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
        foreach(self::$courseTitles as $courseTitle) {
            $course = new Course();
            $course->setTitle($courseTitle);
            $manager->persist($course);
            /*$this->addReference("industry{$i}", $industry);*/
            $i++;
        }

        $manager->flush();
    }
}
