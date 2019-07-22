<?php

namespace App\Command;


use App\Entity\Lesson;
use App\Repository\CareerRepository;
use App\Repository\CourseRepository;
use App\Repository\GradeRepository;
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

class LessonImportCommand extends Command
{
    use FileHelper;

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
     * LessonImportCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param CareerRepository $careerRepository
     * @param GradeRepository $gradeRepository
     * @param CourseRepository $courseRepository
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param UserRepository $userRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CareerRepository $careerRepository,
        GradeRepository $gradeRepository,
        CourseRepository $courseRepository,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->careerRepository = $careerRepository;
        $this->gradeRepository = $gradeRepository;
        $this->courseRepository = $courseRepository;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->userRepository = $userRepository;

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

        $user = $this->userRepository->findOneBy([
            'email' => 'travis@travishoglund.com'
        ]);

        foreach($lessons as $lesson) {

            $lessonObject = new Lesson();
            $lessonObject->setUser($user);

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

            // thumbnail image
            $thumbnailImage = new File(__DIR__.'/ImportedLessonImages/' . $lesson['Thumbnail Image']);

            if($thumbnailImage) {
                $newFilename = $this->fakeUploadImage($lesson['Thumbnail Image'], UploaderHelper::LESSON_THUMBNAIL);
                $lessonObject->setThumbnailImage($newFilename);
                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_THUMBNAIL) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            // featured image
            $featuredImage = new File(__DIR__.'/ImportedLessonImages/' . $lesson['Featured Image']);

            if($featuredImage) {
                $newFilename = $this->fakeUploadImage($lesson['Thumbnail Image'], UploaderHelper::LESSON_FEATURED);
                $lessonObject->setFeaturedImage($newFilename);
                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_FEATURED) .'/'. $newFilename;
            }

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

    public function fakeUploadImage($imageName, $folder): string
    {
        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$imageName;
        $fs->copy(__DIR__.'/ImportedLessonImages/'.$imageName, $targetPath, true);
        return $this->uploaderHelper->upload(new File($targetPath), $folder);
    }

}