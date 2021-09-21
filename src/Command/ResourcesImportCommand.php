<?php

namespace App\Command;


use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\KnowledgeResource;
use App\Entity\Lesson;
use App\Entity\Resource;
use App\Entity\School;
use App\Repository\CompanyRepository;
use App\Repository\CompanyResourceRepository;
use App\Repository\ImageRepository;
use App\Repository\LessonRepository;
use App\Repository\ResourceRepository;
use App\Repository\SchoolResourceRepository;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourcesImportCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:resources:import';

    const DESCRIPTION = 'This command will import resources from a csv';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ImageRepository
     */
    private $imageRepository;

    /**
     * @var ResourceRepository
     */
    private $resourceRepository;

    /**
     * @var CompanyResourceRepository
     */
    private $companyResourceRepository;

    /**
     * @var LessonRepository
     */
    private $lessonRepository;

    /**
     * @var SchoolResourceRepository
     */
    private $schoolResourceRepository;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * ResourcesImportCommand constructor.
     *
     * @param EntityManagerInterface    $entityManager
     * @param ImageRepository           $imageRepository
     * @param ResourceRepository        $resourceRepository
     * @param CompanyResourceRepository $companyResourceRepository
     * @param LessonRepository          $lessonRepository
     * @param SchoolResourceRepository  $schoolResourceRepository
     * @param CompanyRepository         $companyRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ImageRepository $imageRepository,
                                ResourceRepository $resourceRepository,
                                CompanyResourceRepository $companyResourceRepository,
                                LessonRepository $lessonRepository, SchoolResourceRepository $schoolResourceRepository,
                                CompanyRepository $companyRepository
    ) {
        $this->entityManager             = $entityManager;
        $this->imageRepository           = $imageRepository;
        $this->resourceRepository        = $resourceRepository;
        $this->companyResourceRepository = $companyResourceRepository;
        $this->lessonRepository          = $lessonRepository;
        $this->schoolResourceRepository  = $schoolResourceRepository;
        $this->companyRepository         = $companyRepository;

        parent::__construct();
    }


    protected function configure()
    {
        $this->setName(self::COMMAND)
            ->setDescription(
                self::DESCRIPTION
            )
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'The relative path to the csv file is required.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The type of resource we are importing.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {


        // Company Resources:     ./bin/console app:resources:import --path=company_resource.csv --type=company
        // Knowledge Resources:   ./bin/console app:resources:import --path=knowledge_resource.csv --type=knowledge_resource

        $validResourceTypes = [
            'company',
            'knowledge_resource'
            // todo add school and lesson
        ];

        $path = $input->getOption('path');
        $type = $input->getOption('type');

        if(!in_array($type, $validResourceTypes, true)) {
            $output->writeln('Valid resource types are: [company, knowledge_resource]');
            exit(1);
        }

        $output->writeln('Starting resources import!');

        $rowNo = 1;
        $resources = [];

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

                $resources[] = array_combine($keys, $row);
                $rowNo++;
            }
            fclose($fp);
        }


        if($type === 'company') {

            foreach($resources as $resource) {

                /** @var Image $image */
                $image = $this->imageRepository->find($resource['id']);
                $company = $this->companyRepository->find($resource['company_id']);
                $linkToWebsite = in_array($resource['link_to_website'], [null, 'null', 'NULL', ""], true) ? null : $resource['link_to_website'];
                $title = in_array($resource['title'], [null, 'null', 'NULL', ""], true) ? null : $resource['title'];
                $description = in_array($resource['description'], [null, 'null', 'NULL', ""], true) ? null : $resource['description'];

                if(!$company) {
                    continue;
                }

                $resourceObject = new CompanyResource();
                $resourceObject->setCompany($company);
                $resourceObject->setLinkToWebsite($linkToWebsite);
                $resourceObject->setUrl($linkToWebsite);
                $resourceObject->setTitle($title);
                $resourceObject->setDescription($description);
                $resourceObject->setType(Resource::TYPE_URL);
                $resourceObject->setFileName($image->getFileName());
                $resourceObject->setMimeType($image->getMimeType());
                $resourceObject->setOriginalName($image->getOriginalName());

                if($image->getFileName() && $image->getOriginalName() && $image->getMimeType()) {
                    $resourceObject->setType(Resource::TYPE_FILE);
                }


                $this->entityManager->persist($resourceObject);
            }
        }

        if($type === 'knowledge_resource') {

            foreach($resources as $resource) {

                $url = in_array($resource['url'], [null, 'null', 'NULL', ""], true) ? null : $resource['url'];
                $title = in_array($resource['title'], [null, 'null', 'NULL', ""], true) ? null : $resource['title'];
                $description = in_array($resource['description'], [null, 'null', 'NULL', ""], true) ? null : $resource['description'];
                $tab = in_array($resource['tab'], [null, 'null', 'NULL', ""], true) ? null : $resource['tab'];

                $resourceObject = new KnowledgeResource();
                $resourceObject->setLinkToWebsite($url);
                $resourceObject->setUrl($url);
                $resourceObject->setTitle($title);
                $resourceObject->setDescription($description);
                $resourceObject->setTab($tab);
                $resourceObject->setType(Resource::TYPE_URL);

                $this->entityManager->persist($resourceObject);
            }
        }

        $this->entityManager->flush();
        $output->writeln('Resources successfully imported!');
    }
}