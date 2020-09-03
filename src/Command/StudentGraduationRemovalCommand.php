<?php

namespace App\Command;


use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\SecondaryIndustry;
use App\Repository\CourseRepository;
use App\Repository\GradeRepository;
use App\Repository\IndustryRepository;
use App\Repository\SiteRepository;
use App\Repository\StudentUserRepository;
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

class StudentGraduationRemovalCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:student:graduation:removal';

    const DESCRIPTION = 'This command checks to see if there are certain students who have graduated and archives them.';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * StudentGraduationRemovalCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param StudentUserRepository $studentUserRepository
     */
    public function __construct(EntityManagerInterface $entityManager, StudentUserRepository $studentUserRepository)
    {
        $this->entityManager = $entityManager;
        $this->studentUserRepository = $studentUserRepository;

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

        $globalGraduatingDate = "07/31";
        $currentDate = (new \DateTime())->format("m/d");

        if($currentDate !== $globalGraduatingDate) {
            die("this command should only be run on graduation day");
        }

        $studentUsers = $this->studentUserRepository->findBy([
           'graduatingYear' => date("Y")
        ]);

        foreach($studentUsers as $studentUser) {
            $studentUser->setArchived(true);
        }

        $this->entityManager->flush();
        $output->writeln('Graduated students marked as archived!');

    }
}