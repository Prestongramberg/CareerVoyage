<?php

namespace App\DataFixtures;

use App\Entity\Career;
use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RolesWillingToFulfilFixtures extends Fixture
{
    /**
     * @var array
     */
    private $roles = [
        'Guest instructor',
        'Site visit host for students',
        'Field experiences',
        'Informational interviewer',
        'Job shadow host',
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
        foreach($this->roles as $name) {
            $role = new RolesWillingToFulfill();
            $role->setName($name);
            $manager->persist($role);
        }
        $manager->flush();
    }
}
