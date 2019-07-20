<?php

namespace App\Command;


use App\Entity\Lesson;
use App\Repository\CareerRepository;
use App\Repository\CourseRepository;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LessonImportCommand extends Command
{

    const COMMAND = 'lesson:import';

    const DESCRIPTION = 'This command will import lessons from a csv';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CareerRepository
     */
    private $careerRepository;

    /**
     * @var GradeRepository
     */
    private $gradeRepository;

    /**
     * @var CourseRepository
     */
    private $courseRepository;

    /**
     * LessonImportCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param CareerRepository $careerRepository
     * @param GradeRepository $gradeRepository
     * @param CourseRepository $courseRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CareerRepository $careerRepository,
        GradeRepository $gradeRepository,
        CourseRepository $courseRepository
    ) {
        $this->entityManager = $entityManager;
        $this->careerRepository = $careerRepository;
        $this->gradeRepository = $gradeRepository;
        $this->courseRepository = $courseRepository;

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

        $path = $input->getArgument('path');
        $rowNo = 1;
        $lessons = [];
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

                $lessons[] = array_combine($keys, $row);
                $rowNo++;
            }
            fclose($fp);
        }

        foreach($lessons as $lesson) {

            $lessonObject = new Lesson();

            // careers
            $careerIDs = explode(',', $lesson['Career Cluster ID']);
            foreach($careerIDs as $careerID) {
                if(empty($careerID)) continue;
                $career = $this->careerRepository->find($careerID);
                $lessonObject->addCareer($career);
            }

            // grades
            $gradeIDs = explode(',', $lesson['Grade IDs']);
            foreach($gradeIDs as $gradeID) {
                if(empty($gradeID)) continue;
                $grade = $this->gradeRepository->find($gradeID);
                $lessonObject->addGrade($grade);
            }

            $lessonObject->setEducationalStandards($lesson['Lesson Educational Standards']);
            $lessonObject->setLearningOutcomes($lesson['Lesson Learning Outcomes']);
            $lessonObject->setSummary($lesson['Lesson Summary']);
            $lessonObject->setTitle($lesson['Lesson Title']);

            // primary course
            $course = $this->courseRepository->find($lesson['Primary Course ID']);
            $lessonObject->setPrimaryCourse($course);

            // secondary courses
            $secondaryCourseIDs = explode(',', $lesson['Secondary Course IDs']);
            foreach($secondaryCourseIDs as $secondaryCourseID) {

                if(empty($secondaryCourseID)) continue;

                $secondaryCourse = $this->courseRepository->find($secondaryCourseID);
                $lessonObject->addSecondaryCourse($secondaryCourse);
            }

            $this->entityManager->persist($lessonObject);
        }

        $this->entityManager->flush();

        $output->writeln('Lessons successfully imported!');

    }


}