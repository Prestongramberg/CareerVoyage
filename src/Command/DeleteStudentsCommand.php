<?php

namespace App\Command;

use App\Repository\SchoolRepository;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteStudentsCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:delete-students';

    const DESCRIPTION = 'This command deletes students and educators at a given school';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param SchoolRepository       $schoolRepository
     */
    public function __construct(EntityManagerInterface $entityManager, SchoolRepository $schoolRepository)
    {
        $this->entityManager    = $entityManager;
        $this->schoolRepository = $schoolRepository;

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

        $schoolId = 63;
        $school = $this->schoolRepository->find($schoolId);

        if(!$school) {
            $output->writeln('School not found');
            return 1;
        }

        $studentsDeleted = 0;
        $educatorsDeleted = 0;

        $i = 0;
        foreach($school->getStudentUsers() as $studentUser) {
            $this->entityManager->remove($studentUser);
            $studentsDeleted++;

            if($i % 100 === 0) {
                $output->writeln("Removing 100.... Flushing....");
                $this->entityManager->flush();
            }
            $i++;
        }

        $i = 0;
        foreach($school->getEducatorUsers() as $educatorUser) {
            $this->entityManager->remove($educatorUser);
            $educatorsDeleted++;

            if($i % 100 === 0) {
                $output->writeln("Removing 100.... Flushing....");
                $this->entityManager->flush();
            }
            $i++;
        }

        $this->entityManager->flush();

        $output->writeln(sprintf("Total Students Deleted: %s", $studentsDeleted));
        $output->writeln(sprintf("Total Educators Deleted: %s", $educatorsDeleted));
        $output->writeln('Students and Educators successfully deleted...');
    }
}