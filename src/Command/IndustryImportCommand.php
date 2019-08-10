<?php

namespace App\Command;


use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\SecondaryIndustry;
use App\Repository\CourseRepository;
use App\Repository\GradeRepository;
use App\Repository\IndustryRepository;
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

class IndustryImportCommand extends Command
{
    use FileHelper;

    const COMMAND = 'industry:import';

    const DESCRIPTION = 'This command will import primary and secondary industries from a csv';

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
     * SecondaryIndustryImportCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param GradeRepository $gradeRepository
     * @param CourseRepository $courseRepository
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param UserRepository $userRepository
     * @param IndustryRepository $industryRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GradeRepository $gradeRepository,
        CourseRepository $courseRepository,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        UserRepository $userRepository,
        IndustryRepository $industryRepository
    ) {
        $this->entityManager = $entityManager;
        $this->gradeRepository = $gradeRepository;
        $this->courseRepository = $courseRepository;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->userRepository = $userRepository;
        $this->industryRepository = $industryRepository;

        parent::__construct();
    }


    protected function configure()
    {
        $this->setName(self::COMMAND)
            ->setDescription(
                self::DESCRIPTION
            )
            ->addArgument('path', InputArgument::REQUIRED, 'The relative path to the csv file is required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {


        $primaryIndustries = [
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

        foreach($primaryIndustries as $primaryIndustry) {
            $industry = new Industry();
            $industry->setName($primaryIndustry);
            $this->entityManager->persist($industry);
        }
        $this->entityManager->flush();

        $path = $input->getArgument('path');
        $rowNo = 1;
        $industries = [];
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

                $industries[] = array_combine($keys, $row);
                $rowNo++;
            }
            fclose($fp);
        }

        foreach($industries as $industry) {
            $industryObj = new SecondaryIndustry();
            $industryObj->setName($industry['name']);
            $primaryIndustry = $this->industryRepository->find($industry['primary_industry_id']);
            $industryObj->setPrimaryIndustry($primaryIndustry);
            $industryObj->setUrl($industry['url']);
            $this->entityManager->persist($industryObj);
        }

        $this->entityManager->flush();

        $output->writeln('Industries successfully imported!');

    }
}