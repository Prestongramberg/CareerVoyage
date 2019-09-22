<?php

namespace App\Command;


use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolExperience;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Message\RecapMessage;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CourseRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\GradeRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\SchoolExperienceRepository;
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
use Symfony\Component\Messenger\MessageBusInterface;

class SendRecapCommand extends Command
{
    use FileHelper;

    const COMMAND = 'send:recap';

    const DESCRIPTION = 'This command is a central hub for initiating the sending for a recurring email of relevant lessons, events, etc for the users in the system.';

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
     * @var LessonRepository
     */
    private $lessonRepository;

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * @var EducatorUserRepository
     */
    private $educatorUserRepository;

    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

    /**
     * @var CompanyExperienceRepository
     */
    private $companyExperienceRepository;

    /**
     * @var SchoolExperienceRepository
     */
    private $schoolExperienceRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * SendRecapCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param GradeRepository $gradeRepository
     * @param CourseRepository $courseRepository
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param UserRepository $userRepository
     * @param IndustryRepository $industryRepository
     * @param LessonRepository $lessonRepository
     * @param StudentUserRepository $studentUserRepository
     * @param EducatorUserRepository $educatorUserRepository
     * @param ProfessionalUserRepository $professionalUserRepository
     * @param CompanyExperienceRepository $companyExperienceRepository
     * @param SchoolExperienceRepository $schoolExperienceRepository
     * @param MessageBusInterface $bus
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GradeRepository $gradeRepository,
        CourseRepository $courseRepository,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        UserRepository $userRepository,
        IndustryRepository $industryRepository,
        LessonRepository $lessonRepository,
        StudentUserRepository $studentUserRepository,
        EducatorUserRepository $educatorUserRepository,
        ProfessionalUserRepository $professionalUserRepository,
        CompanyExperienceRepository $companyExperienceRepository,
        SchoolExperienceRepository $schoolExperienceRepository,
        MessageBusInterface $bus
    ) {
        $this->entityManager = $entityManager;
        $this->gradeRepository = $gradeRepository;
        $this->courseRepository = $courseRepository;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->userRepository = $userRepository;
        $this->industryRepository = $industryRepository;
        $this->lessonRepository = $lessonRepository;
        $this->studentUserRepository = $studentUserRepository;
        $this->educatorUserRepository = $educatorUserRepository;
        $this->professionalUserRepository = $professionalUserRepository;
        $this->companyExperienceRepository = $companyExperienceRepository;
        $this->schoolExperienceRepository = $schoolExperienceRepository;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName(self::COMMAND)
            ->setDescription(
                self::DESCRIPTION
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $studentUsers = $this->studentUserRepository->findAll();
        $educatorUsers = $this->educatorUserRepository->findAll();
        $professionalUsers = $this->professionalUserRepository->findAll();
        $users = array_merge($studentUsers, $educatorUsers, $professionalUsers);

        // let's go ahead and push each user onto the queue so it's picked up by it's own worker
        /** @var User $user */
        foreach($users as $user) {
            $this->bus->dispatch(new RecapMessage($user->getId()));
        }

        $output->writeln('Recap successfully sent...');
    }
}