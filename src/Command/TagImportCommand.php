<?php

namespace App\Command;

use App\Entity\Tag;
use App\Repository\IndustryRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\TagRepository;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagImportCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:import-tags';

    const DESCRIPTION = 'This command imports primary and secondary industries in as tags.';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IndustryRepository
     */
    private $industryRepository;

    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @param EntityManagerInterface      $entityManager
     * @param IndustryRepository          $industryRepository
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     * @param TagRepository               $tagRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager, IndustryRepository $industryRepository,
        SecondaryIndustryRepository $secondaryIndustryRepository, TagRepository $tagRepository)
    {
        $this->entityManager               = $entityManager;
        $this->industryRepository          = $industryRepository;
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
        $this->tagRepository               = $tagRepository;

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
        $output->writeln("Importing primary industry tags");

        $industries = $this->industryRepository->findAll();

        foreach($industries as $industry) {

            $tag = new Tag();
            $tag->setSystemDefined(true);
            $tag->setPrimaryIndustry($industry);
            $tag->setName($industry->getName());
            $this->entityManager->persist($tag);
        }

        $output->writeln("Importing secondary industry tags");

        $secondaryIndustries = $this->secondaryIndustryRepository->findAll();

        foreach($secondaryIndustries as $secondaryIndustry) {
            $tag = new Tag();
            $tag->setSystemDefined(true);
            $tag->setSecondaryIndustry($secondaryIndustry);
            $tag->setPrimaryIndustry($secondaryIndustry->getPrimaryIndustry());
            $tag->setName($secondaryIndustry->getName());
            $this->entityManager->persist($tag);
        }

        $this->entityManager->flush();

        // todo need to loop through previous experiences and add the industries as tags instead










        $output->writeln("Tag Import complete.");


    }
}