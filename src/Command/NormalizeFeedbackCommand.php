<?php

namespace App\Command;


use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
use App\Entity\Feedback;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolExperience;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Mailer\ChatNotificationMailer;
use App\Message\RecapMessage;
use App\Repository\ChatMessageRepository;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyRepository;
use App\Repository\CourseRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\FeedbackRepository;
use App\Repository\GradeRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\RegionRepository;
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

class NormalizeFeedbackCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:normalize-feedback';

    const DESCRIPTION = 'The feedback entities were setup with discriminator mapping which makes it hard to query off of and filter off of. This command should run hourly/nightly and normalize the feedback data.';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FeedbackRepository
     */
    private $feedbackRepository;

    /**
     * NormalizeFeedbackCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FeedbackRepository     $feedbackRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FeedbackRepository $feedbackRepository)
    {
        $this->entityManager      = $entityManager;
        $this->feedbackRepository = $feedbackRepository;

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

        // todo need to refactor to use more of a doctrine pattern. Also need to make sure it's not eating memory. Look at some of the background
        //  jobs I have built in the past. Also you should calculate the NPS Score here as well.
        $feedbackCollection = $this->generateFeedbackCollection();

        $feedbackUpdateCount = 0;
        /** @var Feedback $feedback */
        foreach($feedbackCollection as $feedback) {

            $className = $feedback->getClassName();

            switch ($className) {

                case 'StudentReviewCompanyExperienceFeedback':
                    /** @var StudentReviewCompanyExperienceFeedback $feedback */

                    $regionIds = [];
                    $regionNames = [];
                    $schoolIds = [];
                    $schoolNames = [];
                    $companyIds = [];
                    $companyNames = [];

                    $feedback->setFeedbackProvider('Student');
                    $feedback->setInterestWorkingForCompany($feedback->getInterestInWorkingForCompany());
                    $feedback->setExperienceProvider('Company');
                    $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                    if($feedback->getExperience() && $feedback->getExperience()->getType()) {
                        $feedback->setExperienceType($feedback->getExperience()->getType());
                        $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                    }

                    if($feedback->getStudent() && $feedback->getStudent()->getSchool() && $region = $feedback->getStudent()->getSchool()->getRegion()) {
                        $regionIds[] = $region->getId();
                        $regionNames[] = $region->getName();
                    }

                    if($feedback->getCompanyExperience() && $company = $feedback->getCompanyExperience()->getCompany()) {
                        foreach($company->getRegions() as $region) {
                            $regionIds[] = $region->getId();
                            $regionNames[] = $region->getName();
                        }
                    }

                    if($feedback->getCompanyExperience() && $employeeContact = $feedback->getCompanyExperience()->getEmployeeContact()) {

                        foreach($employeeContact->getRegions() as $region) {
                            $regionIds[] = $region->getId();
                            $regionNames[] = $region->getName();
                        }
                    }

                    if($feedback->getStudent() && $school = $feedback->getStudent()->getSchool()) {
                        $schoolIds[] = $school->getId();
                        $schoolNames[] = $school->getName();
                    }

                    if($feedback->getCompanyExperience() && $company = $feedback->getCompanyExperience()->getCompany()) {
                        $companyIds[] = $company->getId();
                        $companyNames[] = $company->getName();
                    }

                    $feedback->setRegions($regionIds);
                    $feedback->setRegionNames($regionNames);
                    $feedback->setSchools($schoolIds);
                    $feedback->setSchoolNames($schoolNames);
                    $feedback->setCompanies($companyIds);
                    $feedback->setCompanyNames($companyNames);

                    $feedbackUpdateCount++;
                    break;
            }

            $this->entityManager->persist($feedback);

            if($feedbackUpdateCount % 10 === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        $output->writeln('Feedback Data normalized: ' . $feedbackUpdateCount);
    }

    /**
     * @return iterable
     */
    public function generateFeedbackCollection(): iterable
    {

        $feedbackCollection = $this->feedbackRepository->findAll();

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            yield $feedback;
        }
    }
}