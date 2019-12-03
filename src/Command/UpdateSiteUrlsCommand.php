<?php

namespace App\Command;


use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\SecondaryIndustry;
use App\Repository\CourseRepository;
use App\Repository\GradeRepository;
use App\Repository\IndustryRepository;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class UpdateSiteUrlsCommand extends Command
{
    use FileHelper;

    const COMMAND = 'update:siteUrls';

    const DESCRIPTION = 'This command updates the sites table on your local to contain the correct data after pulling down a prod or staging db to your local for debugging';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GradeRepository
     */
    private $gradeRepository;

    /**
     * @var CourseRepository
     */
    private $courseRepository;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var IndustryRepository
     */
    private $industryRepository;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * UpdateSitesTableOnDevCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param GradeRepository $gradeRepository
     * @param CourseRepository $courseRepository
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param UserRepository $userRepository
     * @param IndustryRepository $industryRepository
     * @param SiteRepository $siteRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GradeRepository $gradeRepository,
        CourseRepository $courseRepository,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        UserRepository $userRepository,
        IndustryRepository $industryRepository,
        SiteRepository $siteRepository
    ) {
        $this->entityManager = $entityManager;
        $this->gradeRepository = $gradeRepository;
        $this->courseRepository = $courseRepository;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->userRepository = $userRepository;
        $this->industryRepository = $industryRepository;
        $this->siteRepository = $siteRepository;

        parent::__construct();
    }


    protected function configure()
    {
        $this->setName(self::COMMAND)
            ->setDescription(
                self::DESCRIPTION
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $site =  $this->siteRepository->findOneBy([
            'fullyQualifiedBaseUrl' => 'https://my.futureforward.org'
        ]);
        if($site) {
            $site->setBaseUrl('my.futureforward.test');
            $site->setFullyQualifiedBaseUrl('http://my.futureforward.test');
        }
        $site =  $this->siteRepository->findOneBy([
            'fullyQualifiedBaseUrl' => 'https://my.pintexsolutions.com'
        ]);
        if($site) {
            $site->setBaseUrl('pintex.test');
            $site->setFullyQualifiedBaseUrl('http://pintex.test');
        }
        $site =  $this->siteRepository->findOneBy([
            'fullyQualifiedBaseUrl' => 'https://dev.futureforward.org'
        ]);
        if($site) {
            $site->setBaseUrl('my.futureforward.test');
            $site->setFullyQualifiedBaseUrl('http://my.futureforward.test');
        }
        $site =  $this->siteRepository->findOneBy([
            'fullyQualifiedBaseUrl' => 'https://dev.pintexsolutions.com'
        ]);
        if($site) {
            $site->setBaseUrl('pintex.test');
            $site->setFullyQualifiedBaseUrl('http://pintex.test');
        }
        $this->entityManager->flush();
        $output->writeln('Site URLs updated for local!');

    }
}