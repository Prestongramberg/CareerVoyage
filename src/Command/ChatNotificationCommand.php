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
use App\Message\UnseenMessagesMessage;
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

class ChatNotificationCommand extends Command
{
    use FileHelper;

    const COMMAND = 'sendChatNotifications';

    const DESCRIPTION = 'This command sends an email of all unseen chat messages within the past hour';

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
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;

    /**
     * @var ChatNotificationMailer
     */
    private $chatNotificationMailer;

    /**
     * ChatNotificationCommand constructor.
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
     * @param ChatMessageRepository $chatMessageRepository
     * @param ChatNotificationMailer $chatNotificationMailer
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
        MessageBusInterface $bus,
        ChatMessageRepository $chatMessageRepository,
        ChatNotificationMailer $chatNotificationMailer
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
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatNotificationMailer = $chatNotificationMailer;

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
        $unreadMessageCounts = $this->chatMessageRepository->getUnreadMessageCountGroupedBySentFromUser();
        $totalUnreadMessageCounts = $this->chatMessageRepository->getTotalUnreadMessageCountGroupedBySentToUser();
        if(!empty($totalUnreadMessageCounts['results'])) {
            foreach ($totalUnreadMessageCounts['results'] as $totalUnreadMessageCount) {
                $userSentToId = $totalUnreadMessageCount['user_sent_to_id'];
                $unreadMessageCountsForUser = array_filter($unreadMessageCounts['results'], function($array) use($userSentToId) {
                    return $array['user_sent_to_id'] === $userSentToId;
                });
                $this->chatNotificationMailer->send($totalUnreadMessageCount, $unreadMessageCountsForUser);
            }
        }

        $output->writeln('Chat notification emails successfully sent...');
    }
}