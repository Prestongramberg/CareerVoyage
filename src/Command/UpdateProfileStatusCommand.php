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
use App\Mailer\ChatNotificationMailer;
use App\Message\RecapMessage;
use App\Repository\ChatMessageRepository;
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

class UpdateProfileStatusCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:update-profile-status';

    const DESCRIPTION = 'This command updates the profile status from already registered members';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UpdateProfileStatusCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserRepository         $userRepository
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager  = $entityManager;
        $this->userRepository = $userRepository;

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

        $users = $this->userRepository->findAll();
        $count = 0;

        foreach ($users as $user) {

            if ($user->isProfileCompleted()) {
                $user->setProfileCompleted(true);
            } else {
                $user->setProfileCompleted(false);
            }

            $this->entityManager->persist($user);

            if ($count % 100 === 0) {
                $this->entityManager->flush();
            }

            $count++;

            $output->writeln('Updated Profile Status For User: ' . $count);
        }

        $this->entityManager->flush();

        $output->writeln('Profiles updated successfully...');
    }
}