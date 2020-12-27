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
use App\Repository\CompanyRepository;
use App\Repository\CourseRepository;
use App\Repository\EducatorUserRepository;
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

class SyncProfessionalAndCompanyRegionCommand extends Command
{
    use FileHelper;

    const COMMAND = 'app:syncProfessionalAndCompanyRegion';

    const DESCRIPTION = 'This sets all the companies and professionals to southeast region';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var RegionRepository
     */
    private $regionRepository;

    /**
     * SyncProfessionalAndCompanyRegionCommand constructor.
     *
     * @param EntityManagerInterface     $entityManager
     * @param ProfessionalUserRepository $professionalUserRepository
     * @param CompanyRepository          $companyRepository
     * @param RegionRepository           $regionRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager, ProfessionalUserRepository $professionalUserRepository,
        CompanyRepository $companyRepository, RegionRepository $regionRepository
    ) {
        $this->entityManager              = $entityManager;
        $this->professionalUserRepository = $professionalUserRepository;
        $this->companyRepository          = $companyRepository;
        $this->regionRepository           = $regionRepository;

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

        $southeastRegion = $this->regionRepository->find(1);

        if(!$southeastRegion) {
            $output->writeln('Southeast region not found.');
            return;
        }

        $professionals = $this->professionalUserRepository->findAll();

        foreach($professionals as $professionalUser) {
            $professionalUser->addRegion($southeastRegion);
        }

        $companies = $this->companyRepository->findAll();

        foreach($companies as $company) {
            $company->addRegion($southeastRegion);
        }

        $this->entityManager->flush();

        $output->writeln('Company and professional region set to southeast.');
    }
}