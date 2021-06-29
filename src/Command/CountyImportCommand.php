<?php

namespace App\Command;


use App\Entity\County;
use App\Entity\Lesson;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Repository\CountyRepository;
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

class CountyImportCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:county-import';

    const DESCRIPTION = 'This command will import counties from a json file.';

    public static $metadata = [

        'Southeast Service Cooperative' => [

            'counties' => [
                'Dodge',
                'Fillmore',
                'Freeborn',
                'Goodhue',
                'Houston',
                'Mower',
                'Olmsted',
                'Rice',
                'Steele',
                'Wabasha',
                'Winona',
            ],
            'region_name' => 'Region 10',
            'color' => '#40e0d0',
        ],

        'Sourcewell (formerly NJPA)' => [

            'counties' => [
                'Cass',
                'Crow Wing',
                'Morrison',
                'Todd',
                'Wadena',
            ],
            'region_name' => 'Region 5',
            'color' => '#964B00',
        ],

        'Lakes Country Service Cooperative' => [

            'counties' => [
                'Becker',
                'Clay',
                'Grant',
                'Douglas',
                'Otter Tail',
                'Pope',
                'Stevens',
                'Traverse',
                'Wilkin',
            ],
            'region_name' => 'Region 4',
            'color' => '#d982b5',
        ],

        'South Central Service Cooperative' => [

            'counties' => [
                'Blue Earth',
                'Brown',
                'Faribault',
                'Le Sueur',
                'Martin',
                'Nicollet',
                'Sibley',
                'Waseca',
                'Watonwan',
            ],
            'region_name' => 'Region 9',
            'color' => '#FFFF66',
        ],

        'Northwest Service Cooperative' => [

            'counties' => [
                'Kittson',
                'Roseau',
                'Lake of the Woods',
                'Beltrami',
                'Pennington',
                'Marshall',
                'Red Lake',
                'Mahnomen',
                'Hubbard',
                'Clearwater',
                'Norman',
                'Polk',
            ],
            'region_name' => 'Region 1 & 2',
            'color' => '#ff6500',
        ],

        'Southwest West Central Service Cooperative' => [

            'counties' => [
                'Cottonwood',
                'Jackson',
                'Lincoln',
                'Lyon',
                'Murray',
                'Nobles',
                'Pipestone',
                'Redwood',
                'Rock',
                'Kandiyohi',
                'McLeod',
                'Meeker',
                'Renville',
                'Big Stone',
                'Chippewa',
                'Lac qui Parle',
                'Swift',
                'Yellow Medicine',
            ],
            'region_name' => 'Region 6 & 8',
            'color' => '#FF0000',
        ],

        'Northeast Service Cooperative' => [

            'counties' => [
                'Aitkin',
                'Carlton',
                'Cook',
                'Itasca',
                'Koochiching',
                'Lake',
                'St. Louis',
            ],
            'region_name' => 'Region 3',
            'color' => '#50C878',
        ],

        'Resource Training Solutions' => [

            'counties' => [
                'Benton',
                'Sherburne',
                'Stearns',
                'Wright',
                'Chisago',
                'Isanti',
                'Kanabec',
                'Mille Lacs',
                'Pine',
            ],
            'region_name' => 'Region 7',
            'color' => '#add8e6',
        ],

        'Metro Educational Service Cooperative ' => [

            'counties' => [
                'Anoka',
                'Carver',
                'Dakota',
                'Hennepin',
                'Ramsey',
                'Scott',
                'Washington',
            ],
            'region_name' => 'Region 11',
            'color' => '#8B8000'
        ],

    ];


    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RegionRepository
     */
    private $regionRepository;

    /**
     * @var StateRepository
     */
    private $stateRepository;

    /**
     * @var CountyRepository
     */
    private $countyRepository;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * CountyImportCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RegionRepository       $regionRepository
     * @param StateRepository        $stateRepository
     * @param CountyRepository       $countyRepository
     * @param string                 $projectDir
     */
    public function __construct(EntityManagerInterface $entityManager, RegionRepository $regionRepository,
                                StateRepository $stateRepository, CountyRepository $countyRepository, string $projectDir
    ) {
        $this->entityManager    = $entityManager;
        $this->regionRepository = $regionRepository;
        $this->stateRepository  = $stateRepository;
        $this->countyRepository = $countyRepository;
        $this->projectDir       = $projectDir;

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

        $this->countyRepository->deleteAllCounties();

        $filePath = sprintf("%s/src/Command/Counties/counties.json", $this->projectDir);
        $json     = file_get_contents($filePath);

        $jsonIterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator(json_decode($json, true)),
            \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($jsonIterator as $key => $val) {

            if ($key !== 'features') {
                continue;
            }

            if (!is_array($val)) {
                continue;
            }

            $i = 0;
            foreach ($val as $key1 => $val1) {

                $stateName  = $val1['properties']['STATE_NAME'];
                $countyName = $val1['properties']['NAME'];

                if ($stateName !== 'Minnesota') {
                    continue;
                }

                if (empty($val1['geometry']['coordinates'])) {
                    continue;
                }

                if (!is_array($val1['geometry']['coordinates'])) {
                    continue;
                }


                $coordinates = $val1['geometry']['coordinates'];

                if (empty($coordinates[0][0])) {
                    continue;
                }

                $county = new County();
                $county->setName($countyName);
                $county->setStateName($stateName);
                $county->setCoordinates($coordinates);
                $county->setColor($this->getColorHex($countyName));
                $county->setRegionName($this->getRegionName($countyName));
                $county->setServiceCooperativeName($this->getServiceCooperativeName($countyName));
                $this->entityManager->persist($county);

                $output->writeln($countyName);

                if ($i % 100 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }

                $i++;
            }
        }

        $this->entityManager->flush();

        $output->writeln('Counties successfully imported!');
    }

    /**
     * @param $countyName
     *
     * @return null|string
     */
    private function getColorHex($countyName)
    {


        foreach (self::$metadata as $serviceCooperativeName => $metadatum) {

            if (empty($metadatum['counties'])) {
                continue;
            }

            if (empty($metadatum['color'])) {
                continue;
            }

            if (!in_array($countyName, $metadatum['counties'], true)) {
                continue;
            }

            return $metadatum['color'];
        }

        return null;
    }

    /**
     * @param $countyName
     *
     * @return null|string
     */
    private function getRegionName($countyName)
    {


        foreach (self::$metadata as $serviceCooperativeName => $metadatum) {

            if (empty($metadatum['counties'])) {
                continue;
            }

            if (empty($metadatum['region_name'])) {
                continue;
            }

            if (!in_array($countyName, $metadatum['counties'], true)) {
                continue;
            }

            return $metadatum['region_name'];
        }

        return null;
    }

    /**
     * @param $countyName
     *
     * @return null|string
     */
    private function getServiceCooperativeName($countyName)
    {


        foreach (self::$metadata as $serviceCooperativeName => $metadatum) {

            if (empty($metadatum['counties'])) {
                continue;
            }

            if (!in_array($countyName, $metadatum['counties'], true)) {
                continue;
            }

            return $serviceCooperativeName;
        }

        return null;
    }
}