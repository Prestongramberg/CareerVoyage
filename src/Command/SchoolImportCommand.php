<?php

namespace App\Command;


use App\Entity\Lesson;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Repository\CourseRepository;
use App\Repository\GradeRepository;
use App\Repository\IndustryRepository;
use App\Repository\RegionRepository;
use App\Repository\SiteRepository;
use App\Repository\StateRepository;
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

class SchoolImportCommand extends Command
{
    use FileHelper;

    const COMMAND = 'school:import';

    const DESCRIPTION = 'This command will import schools from a csv';

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
     * @var RegionRepository
     */
    private $regionRepository;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * @var StateRepository
     */
    private $stateRepository;

    /**
     * SchoolImportCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param GradeRepository $gradeRepository
     * @param CourseRepository $courseRepository
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param UserRepository $userRepository
     * @param IndustryRepository $industryRepository
     * @param RegionRepository $regionRepository
     * @param SiteRepository $siteRepository
     * @param StateRepository $stateRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GradeRepository $gradeRepository,
        CourseRepository $courseRepository,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        UserRepository $userRepository,
        IndustryRepository $industryRepository,
        RegionRepository $regionRepository,
        SiteRepository $siteRepository,
        StateRepository $stateRepository
    ) {
        $this->entityManager = $entityManager;
        $this->gradeRepository = $gradeRepository;
        $this->courseRepository = $courseRepository;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->userRepository = $userRepository;
        $this->industryRepository = $industryRepository;
        $this->regionRepository = $regionRepository;
        $this->siteRepository = $siteRepository;
        $this->stateRepository = $stateRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName(self::COMMAND)
            ->setDescription(
                self::DESCRIPTION
            )
            ->addArgument('path', InputArgument::REQUIRED, 'The relative path to the csv file is required.')
            ->addArgument('regionID', InputArgument::REQUIRED, 'The region id in the database of the region to add the states to.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $path = $input->getArgument('path');
        $rowNo = 1;
        $schools = [];
        if (($fp = fopen($path, "r")) !== FALSE) {
            $keys = [];
            while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
                if($rowNo === 1) {
                    $keys = $row;
                    $rowNo++;
                    continue;
                }

                if(trim(implode('', $row)) == '') {
                    continue;
                }

                if(count($row) !== count($keys)) {
                    $output->writeln("Skipping row {$rowNo} as number of header columns don't match number of row columns!");
                    $rowNo++;
                    continue;
                }

                $schools[] = array_combine($keys, $row);
                $rowNo++;
            }
            fclose($fp);
        }

        foreach($schools as $school) {
            $schoolObj = new School();
            $schoolObj->setName($school['School Districts']);
            $schoolObj->setRegion($this->regionRepository->find(1));
            $schoolObj->setSite($this->siteRepository->find(2));
            $schoolObj->setState($this->stateRepository->find(24));
            $this->entityManager->persist($schoolObj);
        }

        $this->entityManager->flush();
        $output->writeln('Schools successfully imported!');
    }
}