<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Image;
use App\Entity\NewCompanyRequest;
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

class NewCompanyRequestFixtures extends BaseFixture
{
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
        $newCompanyRequest = new NewCompanyRequest();
        $newCompanyRequest->setCreatedBy($this->getReference('user1'));
        $newCompanyRequest->setCompany($this->getReference('company1'));
        $newCompanyRequest->setApproved(true);
        $manager->persist($newCompanyRequest);

        $newCompanyRequest = new NewCompanyRequest();
        $newCompanyRequest->setCreatedBy($this->getReference('user2'));
        $newCompanyRequest->setCompany($this->getReference('company2'));
        $newCompanyRequest->setApproved(true);
        $manager->persist($newCompanyRequest);

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
            UserFixtures::class,
            CompanyFixtures::class
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
