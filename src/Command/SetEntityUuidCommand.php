<?php

namespace App\Command;

use App\Entity\Tag;
use App\Repository\IndustryRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\TagRepository;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetEntityUuidCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:set-entity-uuid';

    const DESCRIPTION = 'This command sets a unique entity uuid on certain entity for security and privacy reasons';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \App\Repository\ExperienceRepository
     */
    private $experienceRepository;

    /**
     * @param  \Doctrine\ORM\EntityManagerInterface  $entityManager
     * @param  \App\Repository\ExperienceRepository  $experienceRepository
     */
    public function __construct(EntityManagerInterface $entityManager, \App\Repository\ExperienceRepository $experienceRepository)
    {
        $this->entityManager        = $entityManager;
        $this->experienceRepository = $experienceRepository;

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
        $output->writeln("Generating uuids for all experiences");

        $experiences = $this->experienceRepository->findAll();

        foreach($experiences as $experience) {

            if($experience->getUuid()) {
                continue;
            }

            $uuid = Uuid::uuid1();
            $experience->setUuid($uuid);

            $this->entityManager->flush();
        }

        $output->writeln("Done generating uuids for all experiences");

        $output->writeln("Uuid generation complete.");
    }
}