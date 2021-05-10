<?php

namespace App\Command;

use App\Cache\CacheKey;
use App\Entity\AdminUser;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use App\Entity\EducatorUser;
use App\Entity\Feedback;
use App\Entity\ProfessionalReviewCompanyExperienceFeedback;
use App\Entity\ProfessionalReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalReviewTeachLessonExperienceFeedback;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\Report;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\SiteAdminUser;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentReviewSchoolExperienceFeedback;
use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\FeedbackRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\ReportRepository;
use App\Repository\SchoolExperienceRepository;
use App\Repository\StudentToMeetProfessionalExperienceRepository;
use App\Repository\StudentUserRepository;
use App\Repository\TeachLessonExperienceRepository;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var CompanyExperienceRepository
     */
    private $companyExperienceRepository;

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * @var EducatorUserRepository
     */
    private $educatorUserRepository;

    /**
     * @var SchoolExperienceRepository
     */
    private $schoolExperienceRepository;

    /**
     * @var StudentToMeetProfessionalExperienceRepository
     */
    private $studentToMeetProfessionalExperienceRepository;

    /**
     * @var TeachLessonExperienceRepository
     */
    private $teachLessonExperienceRepository;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * NormalizeFeedbackCommand constructor.
     *
     * @param EntityManagerInterface                        $entityManager
     * @param FeedbackRepository                            $feedbackRepository
     * @param ProfessionalUserRepository                    $professionalUserRepository
     * @param SerializerInterface                           $serializer
     * @param CompanyRepository                             $companyRepository
     * @param ReportRepository                              $reportRepository
     * @param CompanyExperienceRepository                   $companyExperienceRepository
     * @param StudentUserRepository                         $studentUserRepository
     * @param EducatorUserRepository                        $educatorUserRepository
     * @param SchoolExperienceRepository                    $schoolExperienceRepository
     * @param StudentToMeetProfessionalExperienceRepository $studentToMeetProfessionalExperienceRepository
     * @param TeachLessonExperienceRepository               $teachLessonExperienceRepository
     * @param string                                        $cacheDirectory
     */
    public function __construct(EntityManagerInterface $entityManager, FeedbackRepository $feedbackRepository,
                                ProfessionalUserRepository $professionalUserRepository, SerializerInterface $serializer,
                                CompanyRepository $companyRepository, ReportRepository $reportRepository,
                                CompanyExperienceRepository $companyExperienceRepository,
                                StudentUserRepository $studentUserRepository,
                                EducatorUserRepository $educatorUserRepository,
                                SchoolExperienceRepository $schoolExperienceRepository,
                                StudentToMeetProfessionalExperienceRepository $studentToMeetProfessionalExperienceRepository,
                                TeachLessonExperienceRepository $teachLessonExperienceRepository,
                                string $cacheDirectory
    ) {
        $this->entityManager                                 = $entityManager;
        $this->feedbackRepository                            = $feedbackRepository;
        $this->professionalUserRepository                    = $professionalUserRepository;
        $this->serializer                                    = $serializer;
        $this->companyRepository                             = $companyRepository;
        $this->reportRepository                              = $reportRepository;
        $this->companyExperienceRepository                   = $companyExperienceRepository;
        $this->studentUserRepository                         = $studentUserRepository;
        $this->educatorUserRepository                        = $educatorUserRepository;
        $this->schoolExperienceRepository                    = $schoolExperienceRepository;
        $this->studentToMeetProfessionalExperienceRepository = $studentToMeetProfessionalExperienceRepository;
        $this->teachLessonExperienceRepository               = $teachLessonExperienceRepository;
        $this->cacheDirectory                                = $cacheDirectory;

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

        $this->normalizeFeedbackData($input, $output);

        $this->reportRepository->deleteAllReports();

        $this->normalizeDataForCompaniesRegisteredOnPlatform($input, $output);
        $this->normalizeDataForProfessionalsRegisteredOnPlatform($input, $output);
        $this->normalizeDataForStudentsRegisteredOnPlatform($input, $output);
        $this->normalizeDataForEducatorsRegisteredOnPlatform($input, $output);

        $this->normalizeDataForCompanyExperiences($input, $output);
        $this->normalizeDataForSchoolExperiences($input, $output);
        $this->normalizeDataForStudentParticipation($input, $output);
        $this->normalizeDataForVolunteerParticipation($input, $output);
    }


    private function normalizeFeedbackData(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->clear();
        $output->writeln('Normalizing feedback data.');

        $feedbackUpdateCount = 0;


        $cache = new FilesystemAdapter('feedback', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::FEEDBACK);

        $cache->get(CacheKey::FEEDBACK, function (ItemInterface $item) use (&$feedbackUpdateCount) {

            $cachedFeedback = [];

            foreach ($this->generateFeedbackCollection() as $result) {

                /** @var Feedback $feedback */
                $feedback = $result[0] ?? null;

                if (!$feedback) {
                    continue;
                }

                $className = $feedback->getClassName();

                switch ($className) {

                    // STUDENT IS FEEDBACK PROVIDER AND COMPANY IS EXPERIENCE PROVIDER
                    case 'StudentReviewCompanyExperienceFeedback':
                        /** @var StudentReviewCompanyExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];

                        $feedback->setFeedbackProvider('Student');
                        $feedback->setInterestWorkingForCompany($feedback->getInterestInWorkingForCompany());
                        $feedback->setExperienceProvider('Company');
                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getStudent() && $feedback->getStudent()->getSchool() && $region = $feedback->getStudent()->getSchool()->getRegion()) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();
                            foreach ($region->getRegionalCoordinators() as $regionalCoordinator) {
                                $regionalCoordinators[] = $regionalCoordinator->getId();
                            }
                        }

                        if (($companyExperience = $feedback->getCompanyExperience()) && $employeeContact = $companyExperience->getEmployeeContact()) {
                            $employeeContactIds[] = $employeeContact->getId();
                            $employeeContacts[]   = $employeeContact->getFullName();
                        }

                        if ($feedback->getCompanyExperience() && $company = $feedback->getCompanyExperience()->getCompany()) {
                            foreach ($company->getRegions() as $region) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                            }
                        }

                        if ($feedback->getStudent() && $school = $feedback->getStudent()->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();

                            foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                                $schoolAdmins[] = $schoolAdministrator->getId();
                            }
                        }

                        if ($feedback->getCompanyExperience() && $company = $feedback->getCompanyExperience()->getCompany()) {
                            $companyIds[]   = $company->getId();
                            $companyNames[] = $company->getName();


                            /** @var ProfessionalUser $companyOwner */
                            if ($companyOwner = $company->getOwner()) {
                                $companyAdmins[] = $companyOwner->getId();
                            }

                            if ($companyAdminEmail = $company->getEmailAddress()) {

                                /** @var ProfessionalUser $additionalCompanyAdmin */
                                if ($additionalCompanyAdmin = $this->professionalUserRepository->getByEmailAddress($companyAdminEmail)) {
                                    $companyAdmins[] = $additionalCompanyAdmin->getId();
                                }
                            }
                        }

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('experience_satisfaction');

                        $feedbackUpdateCount++;
                        break;

                    case 'EducatorReviewCompanyExperienceFeedback':
                        /** @var EducatorReviewCompanyExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];


                        $feedback->setFeedbackProvider('Educator');
                        $feedback->setExperienceProvider('Company');
                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getEducator() && $feedback->getEducator()->getSchool() && $region = $feedback->getEducator()->getSchool()->getRegion()) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();

                            foreach ($region->getRegionalCoordinators() as $regionalCoordinator) {
                                $regionalCoordinators[] = $regionalCoordinator->getId();
                            }
                        }

                        if (($companyExperience = $feedback->getCompanyExperience()) && $employeeContact = $companyExperience->getEmployeeContact()) {
                            $employeeContactIds[] = $employeeContact->getId();
                            $employeeContacts[]   = $employeeContact->getFullName();
                        }

                        if ($feedback->getEducator() && $school = $feedback->getEducator()->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();

                            foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                                $schoolAdmins[] = $schoolAdministrator->getId();
                            }
                        }

                        if ($feedback->getCompanyExperience() && $company = $feedback->getCompanyExperience()->getCompany()) {
                            $companyIds[]   = $company->getId();
                            $companyNames[] = $company->getName();

                            /** @var ProfessionalUser $companyOwner */
                            if ($companyOwner = $company->getOwner()) {
                                $companyAdmins[] = $companyOwner->getId();
                            }

                            if ($companyAdminEmail = $company->getEmailAddress()) {

                                /** @var ProfessionalUser $additionalCompanyAdmin */
                                if ($additionalCompanyAdmin = $this->professionalUserRepository->getByEmailAddress($companyAdminEmail)) {
                                    $companyAdmins[] = $additionalCompanyAdmin->getId();
                                }
                            }
                        }

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('experience_satisfaction');

                        $feedbackUpdateCount++;
                        break;

                    case 'ProfessionalReviewCompanyExperienceFeedback':
                        // Feedback Provider is the Professional and the experience provider is the Company
                        /** @var ProfessionalReviewCompanyExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];

                        $feedback->setFeedbackProvider('Professional');
                        $feedback->setExperienceProvider('Company');
                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getCompanyExperience() && $state = $feedback->getCompanyExperience()->getState()) {
                            foreach ($state->getRegions() as $region) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                            }
                        }

                        if ($feedback->getCompanyExperience() && $company = $feedback->getCompanyExperience()->getCompany()) {
                            $companyIds[]   = $company->getId();
                            $companyNames[] = $company->getName();

                            /** @var ProfessionalUser $companyOwner */
                            if ($companyOwner = $company->getOwner()) {
                                $companyAdmins[] = $companyOwner->getId();
                            }

                            if ($companyAdminEmail = $company->getEmailAddress()) {

                                /** @var ProfessionalUser $additionalCompanyAdmin */
                                if ($additionalCompanyAdmin = $this->professionalUserRepository->getByEmailAddress($companyAdminEmail)) {
                                    $companyAdmins[] = $additionalCompanyAdmin->getId();
                                }
                            }
                        }

                        if (($companyExperience = $feedback->getCompanyExperience()) && $employeeContact = $companyExperience->getEmployeeContact()) {
                            $employeeContactIds[] = $employeeContact->getId();
                            $employeeContacts[]   = $employeeContact->getFullName();
                        }

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('experience_satisfaction');

                        $feedbackUpdateCount++;
                        break;

                    case 'StudentReviewSchoolExperienceFeedback':
                        // Feedback Provider is the Student and the experience provider is the School
                        /** @var StudentReviewSchoolExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];

                        $feedback->setFeedbackProvider('Student');
                        $feedback->setExperienceProvider('School');
                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getSchoolExperience() && $school = $feedback->getSchoolExperience()->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();

                            foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                                $schoolAdmins[] = $schoolAdministrator->getId();
                            }

                        }

                        if ($student = $feedback->getStudent()) {
                            if ($school = $student->getSchool()) {
                                $schoolIds[]   = $school->getId();
                                $schoolNames[] = $school->getName();

                                foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                                    $schoolAdmins[] = $schoolAdministrator->getId();
                                }
                            }
                        }

                        if ($feedback->getSchoolExperience() && $school = $feedback->getSchoolExperience()->getSchool()) {
                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();

                                foreach ($region->getRegionalCoordinators() as $regionalCoordinator) {
                                    $regionalCoordinators[] = $regionalCoordinator->getId();
                                }
                            }
                        }

                        if ($student = $feedback->getStudent()) {
                            if ($student->getSchool() && $region = $student->getSchool()->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();

                                foreach ($region->getRegionalCoordinators() as $regionalCoordinator) {
                                    $regionalCoordinators[] = $regionalCoordinator->getId();
                                }
                            }
                        }

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('experience_satisfaction');

                        $feedbackUpdateCount++;
                        break;

                    case 'ProfessionalReviewSchoolExperienceFeedback':
                        // Feedback Provider is the Professional and the experience provider is the School
                        /** @var ProfessionalReviewSchoolExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];

                        $feedback->setFeedbackProvider('Professional');
                        $feedback->setExperienceProvider('School');
                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getSchoolExperience() && $school = $feedback->getSchoolExperience()->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();
                        }

                        if ($feedback->getSchoolExperience() && $school = $feedback->getSchoolExperience()->getSchool()) {
                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                            }
                        }


                        if ($feedback->getProfessional() && $company = $feedback->getProfessional()->getCompany()) {

                            /** @var ProfessionalUser $companyOwner */
                            if ($companyOwner = $company->getOwner()) {
                                $companyAdmins[] = $companyOwner->getId();
                            }

                            if ($companyAdminEmail = $company->getEmailAddress()) {

                                /** @var ProfessionalUser $additionalCompanyAdmin */
                                if ($additionalCompanyAdmin = $this->professionalUserRepository->getByEmailAddress($companyAdminEmail)) {
                                    $companyAdmins[] = $additionalCompanyAdmin->getId();
                                }
                            }
                        }

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('experience_satisfaction');

                        $feedbackUpdateCount++;
                        break;

                    case 'ProfessionalReviewTeachLessonExperienceFeedback':
                        // Feedback Provider is the Professional and the experience provider is the Professional
                        /** @var ProfessionalReviewTeachLessonExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];


                        $feedback->setFeedbackProvider('Professional');
                        $feedback->setExperienceProvider('Professional');
                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getTeachLessonExperience() && $school = $feedback->getTeachLessonExperience()->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();

                            foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                                $schoolAdmins[] = $schoolAdministrator->getId();
                            }

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();

                                foreach ($region->getRegionalCoordinators() as $regionalCoordinator) {
                                    $regionalCoordinators[] = $regionalCoordinator->getId();
                                }
                            }
                        }

                        if ($feedback->getTeachLessonExperience() && $teacher = $feedback->getTeachLessonExperience()->getTeacher()) {

                            $employeeContactIds[] = $teacher->getId();
                            $employeeContacts[]   = $teacher->getFullName();

                            if ($company = $teacher->getCompany()) {
                                $companyIds[]   = $company->getId();
                                $companyNames[] = $company->getName();

                                if ($companyOwner = $company->getOwner()) {
                                    $companyAdmins[] = $companyOwner->getId();
                                }

                                if ($companyAdminEmail = $company->getEmailAddress()) {

                                    if ($additionalCompanyAdmin = $this->professionalUserRepository->getByEmailAddress($companyAdminEmail)) {
                                        $companyAdmins[] = $additionalCompanyAdmin->getId();
                                    }
                                }
                            }
                        }

                        if (($experience = $feedback->getTeachLessonExperience()) && ($request = $experience->getOriginalRequest()) && $lesson = $request->getLesson()) {
                            $feedback->setTopic($lesson->getTitle());

                            if ($teacher = $experience->getTeacher()) {
                                $feedback->setPresenter($teacher->getFullName());
                            }
                        }

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('topic_satisfaction');

                        break;

                    case 'EducatorReviewTeachLessonExperienceFeedback':
                        // Feedback Provider is the Educator and the experience provider is the Professional
                        /** @var EducatorReviewTeachLessonExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];


                        $feedback->setFeedbackProvider('Educator');

                        // todo how do we really determine who the experience provider is? Ask Chris?
                        if ($feedback->getTeachLessonExperience() && $request = $feedback->getTeachLessonExperience()->getOriginalRequest()) {

                            if ($request->getIsFromProfessional()) {
                                $feedback->setExperienceProvider('Company');
                            } elseif (!$request->getIsFromProfessional()) {
                                $feedback->setExperienceProvider('School');
                            }
                        }

                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        // todo we aren't setting a type anywhere.
                        // todo @see Controller/LessonController.php:401
                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getTeachLessonExperience() && $school = $feedback->getTeachLessonExperience()->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();

                            foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                                $schoolAdmins[] = $schoolAdministrator->getId();
                            }

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();

                                foreach ($region->getRegionalCoordinators() as $regionalCoordinator) {
                                    $regionalCoordinators[] = $regionalCoordinator->getId();
                                }
                            }
                        }

                        if ($feedback->getTeachLessonExperience() && $teacher = $feedback->getTeachLessonExperience()->getTeacher()) {

                            $employeeContactIds[] = $teacher->getId();
                            $employeeContacts[]   = $teacher->getFullName();

                            if ($company = $teacher->getCompany()) {
                                $companyIds[]   = $company->getId();
                                $companyNames[] = $company->getName();

                                /** @var ProfessionalUser $companyOwner */
                                if ($companyOwner = $company->getOwner()) {
                                    $companyAdmins[] = $companyOwner->getId();
                                }

                                if ($companyAdminEmail = $company->getEmailAddress()) {

                                    /** @var ProfessionalUser $additionalCompanyAdmin */
                                    if ($additionalCompanyAdmin = $this->professionalUserRepository->getByEmailAddress($companyAdminEmail)) {
                                        $companyAdmins[] = $additionalCompanyAdmin->getId();
                                    }
                                }
                            }
                        }

                        if (($experience = $feedback->getTeachLessonExperience()) && ($request = $experience->getOriginalRequest()) && $lesson = $request->getLesson()) {
                            $feedback->setTopic($lesson->getTitle());

                            if ($teacher = $experience->getTeacher()) {
                                $feedback->setPresenter($teacher->getFullName());
                            }
                        }

                        // the related ro my classroom work is the question for these feedback forms rather than provided career insight
                        $feedback->setRelatedToMyClassroomWork($feedback->getProvidedCareerInsight());

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('topic_satisfaction');

                        break;

                    case 'StudentReviewTeachLessonExperienceFeedback':
                        // Feedback Provider is the Student and the experience provider is the Professional
                        /** @var StudentReviewTeachLessonExperienceFeedback $feedback */

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];


                        $feedback->setFeedbackProvider('Student');

                        // todo how do we really determine who the experience provider is? Ask Chris?
                        if ($feedback->getTeachLessonExperience() && $request = $feedback->getTeachLessonExperience()->getOriginalRequest()) {

                            if ($request->getIsFromProfessional()) {
                                $feedback->setExperienceProvider('Company');
                            } elseif (!$request->getIsFromProfessional()) {
                                $feedback->setExperienceProvider('School');
                            }
                        }

                        $feedback->setEventStartDate($feedback->getExperience()->getStartDateAndTime());

                        // todo we aren't setting a type anywhere.
                        // todo @see Controller/LessonController.php:401
                        if ($feedback->getExperience() && $feedback->getExperience()->getType()) {
                            $feedback->setExperienceType($feedback->getExperience()->getType());
                            $feedback->setExperienceTypeName($feedback->getExperience()->getType()->getEventName());
                        }

                        if ($feedback->getTeachLessonExperience() && $school = $feedback->getTeachLessonExperience()->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();

                            foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                                $schoolAdmins[] = $schoolAdministrator->getId();
                            }

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();

                                foreach ($region->getRegionalCoordinators() as $regionalCoordinator) {
                                    $regionalCoordinators[] = $regionalCoordinator->getId();
                                }
                            }
                        }

                        if ($feedback->getTeachLessonExperience() && $teacher = $feedback->getTeachLessonExperience()->getTeacher()) {

                            $employeeContactIds[] = $teacher->getId();
                            $employeeContacts[]   = $teacher->getFullName();

                            if ($company = $teacher->getCompany()) {
                                $companyIds[]   = $company->getId();
                                $companyNames[] = $company->getName();

                                /** @var ProfessionalUser $companyOwner */
                                if ($companyOwner = $company->getOwner()) {
                                    $companyAdmins[] = $companyOwner->getId();
                                }

                                if ($companyAdminEmail = $company->getEmailAddress()) {

                                    /** @var ProfessionalUser $additionalCompanyAdmin */
                                    if ($additionalCompanyAdmin = $this->professionalUserRepository->getByEmailAddress($companyAdminEmail)) {
                                        $companyAdmins[] = $additionalCompanyAdmin->getId();
                                    }
                                }
                            }
                        }

                        if (($experience = $feedback->getTeachLessonExperience()) && ($request = $experience->getOriginalRequest()) && $lesson = $request->getLesson()) {
                            $feedback->setTopic($lesson->getTitle());

                            if ($teacher = $experience->getTeacher()) {
                                $feedback->setPresenter($teacher->getFullName());
                            }
                        }

                        // the related ro my classroom work is the question for these feedback forms rather than provided career insight
                        $feedback->setRelatedToMyClassroomWork($feedback->getProvidedCareerInsight());

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);
                        $feedback->setDashboardType('topic_satisfaction');

                        break;

                    default:

                        $regionIds            = [];
                        $regionNames          = [];
                        $regionalCoordinators = [];
                        $schoolIds            = [];
                        $schoolNames          = [];
                        $schoolAdmins         = [];
                        $companyIds           = [];
                        $companyNames         = [];
                        $companyAdmins        = [];
                        $employeeContactIds   = [];
                        $employeeContacts     = [];

                        $feedback->setRegions($regionIds);
                        $feedback->setRegionNames($regionNames);
                        $feedback->setRegionalCoordinators($regionalCoordinators);
                        $feedback->setSchools($schoolIds);
                        $feedback->setSchoolNames($schoolNames);
                        $feedback->setSchoolAdmins($schoolAdmins);
                        $feedback->setCompanies($companyIds);
                        $feedback->setCompanyNames($companyNames);
                        $feedback->setCompanyAdmins($companyAdmins);
                        $feedback->setEmployeeContacts($employeeContactIds);
                        $feedback->setEmployeeContactNames($employeeContacts);

                        $feedbackUpdateCount++;

                        break;

                }

                $this->entityManager->persist($feedback);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data             = $this->serializer->serialize($feedback, 'json', ['groups' => ['FEEDBACK']]);
                $data             = json_decode($data, true);
                $cachedFeedback[] = $data;
            }

            return $cachedFeedback;
        });

        $output->writeln('Done..... Count: ' . $feedbackUpdateCount);

        return $this;
    }

    /**
     * @see https://trello.com/c/87FYSdK0/512-companies-registered-on-platfrom
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForCompaniesRegisteredOnPlatform(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Normalizing data for companies registered on platform.');

        $cache = new FilesystemAdapter('companies_registered', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::COMPANIES_REGISTERED);

        $updateCount = 0;

        $cache->get(CacheKey::COMPANIES_REGISTERED, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];
            foreach ($this->generateCompanyCollection() as $result) {

                $schoolIds   = [];
                $schoolNames = [];
                $regionIds   = [];
                $regionNames = [];

                /** @var Company $company */
                $company = $result[0] ?? null;

                if (!$company) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('companies_registered_on_platform');
                $report->setCompanyName($company->getName());
                $report->setCompany($company->getId());
                $report->setRegistrationDate($company->getCreatedAt());

                foreach ($company->getSchools() as $school) {
                    $schoolNames[] = $school->getName();
                    $schoolIds[]   = $school->getId();

                    if ($region = $school->getRegion()) {
                        $regionNames[] = $region->getName();
                        $regionIds[]   = $region->getId();
                    }
                }

                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegionNames($regionNames);
                $report->setRegions($regionIds);

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }

            return $cachedData;
        });

        $output->writeln('Done with report ("companies_registered_on_platform") Count: ' . $updateCount);

        return $this;
    }

    /**
     * @see https://trello.com/c/vVvXl7MV/513-professionals-registered-on-platform-report
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForProfessionalsRegisteredOnPlatform(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Normalizing data for professionals registered on platform.');

        $cache = new FilesystemAdapter('professionals_registered', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::PROFESSIONALS_REGISTERED);

        $updateCount = 0;

        $cache->get(CacheKey::PROFESSIONALS_REGISTERED, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];
            foreach ($this->generateProfessionalCollection() as $result) {

                $schoolIds   = [];
                $schoolNames = [];
                $regionNames = [];
                $regionIds   = [];

                /** @var ProfessionalUser $professional */
                $professional = $result[0] ?? null;

                if (!$professional) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('professionals_registered_on_platform');
                $report->setCompanyName($professional->getCompany() ? $professional->getCompany()->getName() : null);
                $report->setCompany($professional->getCompany() ? $professional->getCompany()->getId() : null);
                $report->setRegistrationDate($professional->getCreatedAt());
                $report->setProfessional($professional->getId());
                $report->setProfessionalName($professional->getFullName());

                foreach ($professional->getSchools() as $school) {
                    $schoolNames[] = $school->getName();
                    $schoolIds[]   = $school->getId();

                    if ($region = $school->getRegion()) {
                        $regionNames[] = $region->getName();
                        $regionIds[]   = $region->getId();
                    }
                }

                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegionNames($regionNames);
                $report->setRegions($regionIds);

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }

            return $cachedData;
        });


        $output->writeln('Done with report ("professionals_registered_on_platform") Count: ' . $updateCount);

        return $this;
    }

    /**
     * @see https://trello.com/c/WVp4YdDp/515-student-registered-on-platform-report
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForStudentsRegisteredOnPlatform(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Normalizing data for students registered on platform.');

        $cache = new FilesystemAdapter('students_registered', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::STUDENTS_REGISTERED);

        $updateCount = 0;
        $cache->get(CacheKey::STUDENTS_REGISTERED, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];

            foreach ($this->generateStudentCollection() as $result) {

                $schoolIds   = [];
                $schoolNames = [];
                $regionIds   = [];
                $regionNames = [];

                /** @var StudentUser $student */
                $student = $result[0] ?? null;

                if (!$student) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('students_registered_on_platform');
                $report->setRegistrationDate($student->getCreatedAt());

                if ($school = $student->getSchool()) {
                    $schoolNames[] = $school->getName();
                    $schoolIds[]   = $school->getId();
                    $report->setSchoolName($school->getName());
                    $report->setSchool($school->getId());

                    if ($region = $school->getRegion()) {
                        $regionIds[]   = $region->getId();
                        $regionNames[] = $region->getName();
                        $report->setRegion($region->getId());
                        $report->setRegionName($region->getName());
                    }
                }


                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegions($regionIds);
                $report->setRegionNames($regionNames);
                $report->setStudent($student->getId());
                $report->setStudentName($student->getFullName());

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }

            return $cachedData;

        });

        $output->writeln('Done with report ("students_registered_on_platform") Count: ' . $updateCount);

        return $this;
    }

    /**
     * @see https://trello.com/c/09EWHk5g/514-educators-registered-on-platform-report
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForEducatorsRegisteredOnPlatform(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Normalizing data for educators registered on platform.');

        $cache = new FilesystemAdapter('educators_registered', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::EDUCATORS_REGISTERED);

        $updateCount = 0;
        $cache->get(CacheKey::EDUCATORS_REGISTERED, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];

            foreach ($this->generateEducatorCollection() as $result) {

                $schoolIds   = [];
                $schoolNames = [];
                $regionIds   = [];
                $regionNames = [];

                /** @var EducatorUser $educator */
                $educator = $result[0] ?? null;

                if (!$educator) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('educators_registered_on_platform');
                $report->setRegistrationDate($educator->getCreatedAt());

                if ($school = $educator->getSchool()) {
                    $schoolNames[] = $school->getName();
                    $schoolIds[]   = $school->getId();
                    $report->setSchoolName($school->getName());
                    $report->setSchool($school->getId());

                    if ($region = $school->getRegion()) {
                        $regionIds[]   = $region->getId();
                        $regionNames[] = $region->getName();
                        $report->setRegion($region->getId());
                        $report->setRegionName($region->getName());
                    }
                }

                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegions($regionIds);
                $report->setRegionNames($regionNames);
                $report->setEducator($educator->getId());
                $report->setEducatorName($educator->getFullName());

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }

            return $cachedData;
        });


        $output->writeln('Done with report ("educators_registered_on_platform") Count: ' . $updateCount);

        return $this;
    }

    /**
     * @see https://trello.com/c/F2V69ziu/516-company-experiences-report
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForCompanyExperiences(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Normalizing data for company experiences.');

        $cache = new FilesystemAdapter('company_experience_participation', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::COMPANY_EXPERIENCE_PARTICIPATION);

        $updateCount = 0;

        $cache->get(CacheKey::COMPANY_EXPERIENCE_PARTICIPATION, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];

            foreach ($this->generateCompanyExperienceCollection() as $result) {

                $schoolIds   = [];
                $schoolNames = [];
                $regionIds   = [];
                $regionNames = [];

                /** @var CompanyExperience $companyExperience */
                $companyExperience = $result[0] ?? null;

                if (!$companyExperience) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('company_experience_participation');
                $report->setCompanyName($companyExperience->getCompany() ? $companyExperience->getCompany()->getName() : null);
                $report->setCompany($companyExperience->getCompany() ? $companyExperience->getCompany()->getId() : null);
                $report->setExperienceStartDate($companyExperience->getStartDateAndTime());
                $report->setExperienceName($companyExperience->getTitle());
                $report->setExperience($companyExperience->getId());
                $report->setParticipationType('Company');

                if ($type = $companyExperience->getType()) {
                    $report->setExperienceType($companyExperience->getType()->getName());
                    $report->setExperienceTypeId($companyExperience->getType()->getId());
                }

                if ($companyExperience->getRegistrations()->count() === 0) {
                    continue;
                }

                foreach ($companyExperience->getRegistrations() as $registration) {

                    if (!$registeredUser = $registration->getUser()) {
                        continue;
                    }

                    if ($registeredUser instanceof EducatorUser) {

                        if ($school = $registeredUser->getSchool()) {
                            $schoolIds[]   = $registeredUser->getSchool()->getId();
                            $schoolNames[] = $registeredUser->getSchool()->getName();

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                            }
                        } else {
                            continue;
                        }
                    } elseif ($registeredUser instanceof StudentUser) {

                        if ($school = $registeredUser->getSchool()) {
                            $schoolIds[]   = $registeredUser->getSchool()->getId();
                            $schoolNames[] = $registeredUser->getSchool()->getName();

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                            }
                        } else {
                            continue;
                        }
                    } elseif ($registeredUser instanceof SchoolAdministrator) {

                        foreach ($registeredUser->getSchools() as $school) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                            }
                        }
                    } elseif ($registeredUser instanceof ProfessionalUser) {

                        foreach ($registeredUser->getSchools() as $school) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();
                        }

                        foreach ($registeredUser->getRegions() as $region) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();
                        }
                    } elseif ($registeredUser instanceof AdminUser) {
                        continue;
                    } elseif ($registeredUser instanceof SiteAdminUser) {
                        continue;
                    } elseif ($registeredUser instanceof RegionalCoordinator) {
                        continue;
                    }
                }

                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegionNames($regionNames);
                $report->setRegions($regionIds);

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }

            return $cachedData;
        });

        $output->writeln('Done with report ("company_experience_participation") Count: ' . $updateCount);

        return $this;
    }


    /**
     * @see https://trello.com/c/I7iGQ6Nv/517-school-experiences-report
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForSchoolExperiences(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Normalizing data for school experiences.');

        $cache = new FilesystemAdapter('school_experience_participation', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::SCHOOL_EXPERIENCE_PARTICIPATION);

        $updateCount = 0;

        $cache->get(CacheKey::SCHOOL_EXPERIENCE_PARTICIPATION, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];

            foreach ($this->generateSchoolExperienceCollection() as $result) {

                $schoolIds   = [];
                $schoolNames = [];
                $regionIds   = [];
                $regionNames = [];

                /** @var SchoolExperience $schoolExperience */
                $schoolExperience = $result[0] ?? null;

                if (!$schoolExperience) {
                    continue;
                }

                if (!$schoolExperience->getSchool()) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('school_experience_participation');
                $report->setSchoolName($schoolExperience->getSchool() ? $schoolExperience->getSchool()->getName() : null);
                $report->setSchool($schoolExperience->getSchool() ? $schoolExperience->getSchool()->getId() : null);
                $report->setExperienceStartDate($schoolExperience->getStartDateAndTime());
                $report->setExperienceName($schoolExperience->getTitle());
                $report->setExperience($schoolExperience->getId());
                $report->setParticipationType('School');

                if ($type = $schoolExperience->getType()) {
                    $report->setExperienceType($schoolExperience->getType()->getName());
                    $report->setExperienceTypeId($schoolExperience->getType()->getId());
                }

                if ($school = $schoolExperience->getSchool()) {
                    $report->setSchool($school->getId());
                    $report->setSchoolName($school->getName());
                    $schoolIds[]   = $school->getId();
                    $schoolNames[] = $school->getName();

                    if ($region = $school->getRegion()) {
                        $report->setRegionName($region->getName());
                        $report->setRegion($region->getId());
                        $regionIds[]   = $region->getId();
                        $regionNames[] = $region->getName();
                    }
                }

                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegionNames($regionNames);
                $report->setRegions($regionIds);

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }

            return $cachedData;
        });

        $output->writeln('Done with report ("school_experience_participation") Count: ' . $updateCount);

        return $this;
    }

    /**
     * @see https://trello.com/c/L509zwUT/539-student-participation-reports
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForStudentParticipation(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Normalizing data for student experience participation.');

        $cache = new FilesystemAdapter('student_experience_participation', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::STUDENT_EXPERIENCE_PARTICIPATION);

        $updateCount = 0;

        $cache->get(CacheKey::STUDENT_EXPERIENCE_PARTICIPATION, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];

            foreach ($this->generateCompanyExperienceCollection() as $result) {

                /** @var CompanyExperience $companyExperience */
                $companyExperience = $result[0] ?? null;

                if (!$companyExperience) {
                    continue;
                }

                if ($companyExperience->getRegistrations()->count() === 0) {
                    continue;
                }

                foreach ($companyExperience->getRegistrations() as $registration) {

                    $schoolIds   = [];
                    $schoolNames = [];
                    $regionIds   = [];
                    $regionNames = [];

                    if (!$registeredUser = $registration->getUser()) {
                        continue;
                    }

                    if (!$registeredUser instanceof StudentUser) {
                        continue;
                    }

                    $report = new Report();
                    $report->setDashboardType('student_experience_participation');
                    $report->setExperienceStartDate($companyExperience->getStartDateAndTime());
                    $report->setExperienceName($companyExperience->getTitle());
                    $report->setExperience($companyExperience->getId());
                    $report->setStudent($registeredUser->getId());
                    $report->setStudentName($registeredUser->getFullName());
                    $report->setParticipationType('Student');

                    if ($type = $companyExperience->getType()) {
                        $report->setExperienceType($companyExperience->getType()->getName());
                        $report->setExperienceTypeId($companyExperience->getType()->getId());
                    }

                    if ($school = $registeredUser->getSchool()) {
                        $schoolIds[]   = $registeredUser->getSchool()->getId();
                        $schoolNames[] = $registeredUser->getSchool()->getName();
                        $report->setSchool($school->getId());
                        $report->setSchoolName($school->getName());

                        if ($region = $school->getRegion()) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();
                            $report->setRegion($region->getId());
                            $report->setRegionName($region->getName());
                        }
                    }

                    $report->setSchoolNames($schoolNames);
                    $report->setSchools($schoolIds);
                    $report->setRegionNames($regionNames);
                    $report->setRegions($regionIds);

                    $this->entityManager->persist($report);
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                    $data         = json_decode($data, true);
                    $cachedData[] = $data;

                    $updateCount++;
                }
            }


            foreach ($this->generateSchoolExperienceCollection() as $result) {

                /** @var SchoolExperience $schoolExperience */
                $schoolExperience = $result[0] ?? null;

                if (!$schoolExperience) {
                    continue;
                }

                if ($schoolExperience->getRegistrations()->count() === 0) {
                    continue;
                }

                foreach ($schoolExperience->getRegistrations() as $registration) {

                    $schoolIds   = [];
                    $schoolNames = [];
                    $regionIds   = [];
                    $regionNames = [];

                    if (!$registeredUser = $registration->getUser()) {
                        continue;
                    }

                    if (!$registeredUser instanceof StudentUser) {
                        continue;
                    }

                    $report = new Report();
                    $report->setDashboardType('student_experience_participation');
                    $report->setExperienceStartDate($schoolExperience->getStartDateAndTime());
                    $report->setExperienceName($schoolExperience->getTitle());
                    $report->setExperience($schoolExperience->getId());
                    $report->setStudent($registeredUser->getId());
                    $report->setStudentName($registeredUser->getFullName());
                    $report->setParticipationType('Student');

                    if ($type = $schoolExperience->getType()) {
                        $report->setExperienceType($schoolExperience->getType()->getName());
                        $report->setExperienceTypeId($schoolExperience->getType()->getId());
                    }

                    if ($school = $registeredUser->getSchool()) {
                        $schoolIds[]   = $registeredUser->getSchool()->getId();
                        $schoolNames[] = $registeredUser->getSchool()->getName();
                        $report->setSchool($school->getId());
                        $report->setSchoolName($school->getName());

                        if ($region = $school->getRegion()) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();
                            $report->setRegion($region->getId());
                            $report->setRegionName($region->getName());
                        }
                    }

                    $report->setSchoolNames($schoolNames);
                    $report->setSchools($schoolIds);
                    $report->setRegionNames($regionNames);
                    $report->setRegions($regionIds);

                    $this->entityManager->persist($report);
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                    $data         = json_decode($data, true);
                    $cachedData[] = $data;

                    $updateCount++;
                }
            }


            foreach ($this->generateStudentToMeetProfessionalExperienceCollection() as $result) {

                /** @var StudentToMeetProfessionalExperience $studentToMeetProfessionalExperience */
                $studentToMeetProfessionalExperience = $result[0] ?? null;

                if (!$studentToMeetProfessionalExperience) {
                    continue;
                }

                if ($studentToMeetProfessionalExperience->getRegistrations()->count() === 0) {
                    continue;
                }

                foreach ($studentToMeetProfessionalExperience->getRegistrations() as $registration) {

                    $schoolIds   = [];
                    $schoolNames = [];
                    $regionIds   = [];
                    $regionNames = [];

                    if (!$registeredUser = $registration->getUser()) {
                        continue;
                    }

                    if (!$registeredUser instanceof StudentUser) {
                        continue;
                    }

                    $report = new Report();
                    $report->setDashboardType('student_experience_participation');
                    $report->setExperienceStartDate($studentToMeetProfessionalExperience->getStartDateAndTime());
                    $report->setExperienceName($studentToMeetProfessionalExperience->getTitle());
                    $report->setExperience($studentToMeetProfessionalExperience->getId());
                    $report->setStudent($registeredUser->getId());
                    $report->setStudentName($registeredUser->getFullName());
                    $report->setParticipationType('Student');

                    if ($type = $studentToMeetProfessionalExperience->getType()) {
                        $report->setExperienceType($studentToMeetProfessionalExperience->getType()->getName());
                        $report->setExperienceTypeId($studentToMeetProfessionalExperience->getType()->getId());
                    }

                    if ($school = $registeredUser->getSchool()) {
                        $schoolIds[]   = $registeredUser->getSchool()->getId();
                        $schoolNames[] = $registeredUser->getSchool()->getName();
                        $report->setSchool($school->getId());
                        $report->setSchoolName($school->getName());

                        if ($region = $school->getRegion()) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();
                            $report->setRegion($region->getId());
                            $report->setRegionName($region->getName());
                        }
                    }

                    $report->setSchoolNames($schoolNames);
                    $report->setSchools($schoolIds);
                    $report->setRegionNames($regionNames);
                    $report->setRegions($regionIds);

                    $this->entityManager->persist($report);
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                    $data         = json_decode($data, true);
                    $cachedData[] = $data;

                    $updateCount++;
                }
            }


            return $cachedData;
        });

        $output->writeln('Done with report ("student_experience_participation") Count: ' . $updateCount);

        return $this;
    }

    /**
     * @see https://trello.com/c/5UymjA9K/538-volunteer-participation-report
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return NormalizeFeedbackCommand
     */
    private function normalizeDataForVolunteerParticipation(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Normalizing data for professional volunteer participation.');

        $cache = new FilesystemAdapter('volunteer_experience_participation', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::VOLUNTEER_EXPERIENCE_PARTICIPATION);

        $updateCount = 0;

        $cache->get(CacheKey::VOLUNTEER_EXPERIENCE_PARTICIPATION, function (ItemInterface $item) use (&$updateCount) {

            $cachedData = [];

            foreach ($this->generateCompanyExperienceCollection() as $result) {

                /** @var CompanyExperience $companyExperience */
                $companyExperience = $result[0] ?? null;

                if (!$companyExperience) {
                    continue;
                }

                if (!$employeeContact = $companyExperience->getEmployeeContact()) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('professional_volunteer_participation');
                $report->setExperienceStartDate($companyExperience->getStartDateAndTime());
                $report->setExperienceName($companyExperience->getTitle());
                $report->setExperience($companyExperience->getId());
                $report->setProfessional($employeeContact->getId());
                $report->setProfessionalName($employeeContact->getFullName());
                $report->setParticipationType('Volunteer');

                if ($type = $companyExperience->getType()) {
                    $report->setExperienceType($companyExperience->getType()->getName());
                    $report->setExperienceTypeId($companyExperience->getType()->getId());
                }

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }


            foreach ($this->generateSchoolExperienceCollection() as $result) {

                /** @var SchoolExperience $schoolExperience */
                $schoolExperience = $result[0] ?? null;

                if (!$schoolExperience) {
                    continue;
                }

                if ($schoolExperience->getRegistrations()->count() === 0) {
                    continue;
                }

                foreach ($schoolExperience->getRegistrations() as $registration) {

                    $schoolIds   = [];
                    $schoolNames = [];
                    $regionIds   = [];
                    $regionNames = [];

                    if (!$registeredUser = $registration->getUser()) {
                        continue;
                    }

                    if (!$registeredUser instanceof ProfessionalUser) {
                        continue;
                    }

                    $report = new Report();
                    $report->setDashboardType('professional_volunteer_participation');
                    $report->setExperienceStartDate($schoolExperience->getStartDateAndTime());
                    $report->setExperienceName($schoolExperience->getTitle());
                    $report->setExperience($schoolExperience->getId());
                    $report->setProfessional($registeredUser->getId());
                    $report->setProfessionalName($registeredUser->getFullName());
                    $report->setParticipationType('Volunteer');

                    if ($type = $schoolExperience->getType()) {
                        $report->setExperienceType($schoolExperience->getType()->getName());
                        $report->setExperienceTypeId($schoolExperience->getType()->getId());
                    }

                    if ($school = $schoolExperience->getSchool()) {
                        $report->setSchool($school->getId());
                        $report->setSchoolName($school->getName());
                        $schoolIds[]   = $school->getId();
                        $schoolNames[] = $school->getName();

                        if ($region = $school->getRegion()) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();
                        }
                    }

                    $report->setSchoolNames($schoolNames);
                    $report->setSchools($schoolIds);
                    $report->setRegionNames($regionNames);
                    $report->setRegions($regionIds);

                    $this->entityManager->persist($report);
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                    $data         = json_decode($data, true);
                    $cachedData[] = $data;

                    $updateCount++;
                }
            }


            foreach ($this->generateStudentToMeetProfessionalExperienceCollection() as $result) {

                /** @var StudentToMeetProfessionalExperience $studentToMeetProfessionalExperience */
                $studentToMeetProfessionalExperience = $result[0] ?? null;

                if (!$studentToMeetProfessionalExperience) {
                    continue;
                }

                if ($studentToMeetProfessionalExperience->getRegistrations()->count() === 0) {
                    continue;
                }

                foreach ($studentToMeetProfessionalExperience->getRegistrations() as $registration) {

                    if (!$registeredUser = $registration->getUser()) {
                        continue;
                    }

                    if (!$registeredUser instanceof ProfessionalUser) {
                        continue;
                    }

                    $report = new Report();
                    $report->setDashboardType('professional_volunteer_participation');
                    $report->setExperienceStartDate($studentToMeetProfessionalExperience->getStartDateAndTime());
                    $report->setExperienceName($studentToMeetProfessionalExperience->getTitle());
                    $report->setExperience($studentToMeetProfessionalExperience->getId());
                    $report->setProfessional($registeredUser->getId());
                    $report->setProfessionalName($registeredUser->getFullName());
                    $report->setParticipationType('Volunteer');

                    if ($type = $studentToMeetProfessionalExperience->getType()) {
                        $report->setExperienceType($studentToMeetProfessionalExperience->getType()->getName());
                        $report->setExperienceTypeId($studentToMeetProfessionalExperience->getType()->getId());
                    }

                    $this->entityManager->persist($report);
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                    $data         = json_decode($data, true);
                    $cachedData[] = $data;

                    $updateCount++;
                }
            }


            foreach ($this->generateTeachLessonExperienceCollection() as $result) {

                $schoolIds   = [];
                $schoolNames = [];
                $regionIds   = [];
                $regionNames = [];

                /** @var TeachLessonExperience $teachLessonExperience */
                $teachLessonExperience = $result[0] ?? null;

                if (!$teachLessonExperience) {
                    continue;
                }

                $report = new Report();
                $report->setDashboardType('professional_volunteer_participation');
                $report->setExperienceStartDate($teachLessonExperience->getStartDateAndTime());
                $report->setExperienceName($teachLessonExperience->getTitle());
                $report->setExperience($teachLessonExperience->getId());
                $report->setParticipationType('Volunteer');

                if ($type = $teachLessonExperience->getType()) {
                    $report->setExperienceType($teachLessonExperience->getType()->getName());
                    $report->setExperienceTypeId($teachLessonExperience->getType()->getId());
                }

                if ($teacher = $teachLessonExperience->getTeacher()) {
                    $report->setProfessional($teacher->getId());
                    $report->setProfessionalName($teacher->getFullName());
                }

                if ($school = $teachLessonExperience->getSchool()) {
                    $schoolNames[] = $school->getName();
                    $schoolIds[]   = $school->getId();
                    $report->setSchool($school->getId());
                    $report->setSchoolName($school->getName());

                    if ($region = $school->getRegion()) {
                        $regionIds[]   = $region->getId();
                        $regionNames[] = $region->getName();
                        $report->setRegion($region->getId());
                        $report->setRegionName($region->getName());
                    }
                }

                $report->setSchools($schoolIds);
                $report->setSchoolNames($schoolNames);
                $report->setRegions($regionIds);
                $report->setRegionNames($regionNames);

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $data         = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);
                $data         = json_decode($data, true);
                $cachedData[] = $data;

                $updateCount++;
            }

            return $cachedData;
        });

        $output->writeln('Done with report ("professional_volunteer_participation") Count: ' . $updateCount);

        return $this;
    }

    /**
     * @return iterable
     */
    private function generateFeedbackCollection(): iterable
    {
        $queryBuilder = $this->feedbackRepository->createQueryBuilder('f')->getQuery();

        /** @var Feedback $feedback */
        foreach ($queryBuilder->iterate() as $feedback) {

            yield $feedback;
        }
    }

    /**
     * @return iterable
     */
    private function generateCompanyCollection(): iterable
    {
        $queryBuilder = $this->companyRepository->createQueryBuilder('c')->getQuery();

        /** @var Company $company */
        foreach ($queryBuilder->iterate() as $company) {

            yield $company;
        }
    }

    /**
     * @return iterable
     */
    private function generateProfessionalCollection(): iterable
    {
        $queryBuilder = $this->professionalUserRepository->createQueryBuilder('p')->getQuery();

        /** @var ProfessionalUser $professionalUser */
        foreach ($queryBuilder->iterate() as $professionalUser) {

            yield $professionalUser;
        }
    }

    /**
     * @return iterable
     */
    private function generateCompanyExperienceCollection(): iterable
    {
        $queryBuilder = $this->companyExperienceRepository->createQueryBuilder('ce')->getQuery();

        /** @var CompanyExperience $companyExperience */
        foreach ($queryBuilder->iterate() as $companyExperience) {

            yield $companyExperience;
        }
    }

    /**
     * @return iterable
     */
    private function generateStudentCollection(): iterable
    {
        $queryBuilder = $this->studentUserRepository->createQueryBuilder('su')->getQuery();

        /** @var StudentUser $studentUser */
        foreach ($queryBuilder->iterate() as $studentUser) {

            yield $studentUser;
        }
    }

    /**
     * @return iterable
     */
    private function generateSchoolExperienceCollection(): iterable
    {
        $queryBuilder = $this->schoolExperienceRepository->createQueryBuilder('se')->getQuery();

        /** @var SchoolExperience $schoolExperience */
        foreach ($queryBuilder->iterate() as $schoolExperience) {

            yield $schoolExperience;
        }
    }

    /**
     * @return iterable
     */
    private function generateEducatorCollection(): iterable
    {
        $queryBuilder = $this->educatorUserRepository->createQueryBuilder('eu')->getQuery();

        /** @var EducatorUser $educatorUser */
        foreach ($queryBuilder->iterate() as $educatorUser) {

            yield $educatorUser;
        }
    }

    /**
     * @return iterable
     */
    private function generateStudentToMeetProfessionalExperienceCollection(): iterable
    {
        $queryBuilder = $this->studentToMeetProfessionalExperienceRepository->createQueryBuilder('spe')->getQuery();

        /** @var StudentToMeetProfessionalExperience $studentToMeetProfessionalExperience */
        foreach ($queryBuilder->iterate() as $studentToMeetProfessionalExperience) {

            yield $studentToMeetProfessionalExperience;
        }
    }

    /**
     * @return iterable
     */
    private function generateTeachLessonExperienceCollection(): iterable
    {
        $queryBuilder = $this->teachLessonExperienceRepository->createQueryBuilder('tle')->getQuery();

        /** @var TeachLessonExperience $teachLessonExperience */
        foreach ($queryBuilder->iterate() as $teachLessonExperience) {

            yield $teachLessonExperience;
        }
    }
}