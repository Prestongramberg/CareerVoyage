<?php

namespace App\Command;

use App\Cache\CacheKey;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalReviewCompanyExperienceFeedback;
use App\Entity\ProfessionalReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalReviewTeachLessonExperienceFeedback;
use App\Entity\ProfessionalUser;
use App\Entity\Registration;
use App\Entity\Report;
use App\Entity\ReportLessonsCanTeach;
use App\Entity\ReportLessonsWantTaught;
use App\Entity\ReportVolunteerRegion;
use App\Entity\ReportVolunteerRole;
use App\Entity\ReportVolunteerSchool;
use App\Entity\SchoolExperience;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentReviewSchoolExperienceFeedback;
use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\ExperienceRepository;
use App\Repository\FeedbackRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\RegistrationRepository;
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
     * @var RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var ExperienceRepository
     */
    private $experienceRepository;


    /**
     * @var LessonTeachableRepository
     */
    private $lessonTeachableRepository;

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
     * @param RegistrationRepository                        $registrationRepository
     * @param ExperienceRepository                          $experienceRepository
     * @param LessonTeachableRepository                     $lessonTeachableRepository
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
                                RegistrationRepository $registrationRepository,
                                ExperienceRepository $experienceRepository,
                                LessonTeachableRepository $lessonTeachableRepository,
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
        $this->registrationRepository                        = $registrationRepository;
        $this->experienceRepository                          = $experienceRepository;
        $this->lessonTeachableRepository                     = $lessonTeachableRepository;
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
        $this->reportRepository->deleteAllDashboardReports();
        $this->reportRepository->deleteReportVolunteerSchoolData();
        $this->reportRepository->deleteReportVolunteerRegionData();
        $this->reportRepository->deleteReportVolunteerRoleData();

        // add company administrator roles to company owners
        $this->addCompanyAdministratorRoles($input, $output);

        // report experience data normalization
        $this->normalizeExperienceData($input, $output);
        $this->normalizeProfessionalUserData($input, $output);
        $this->normalizeEducatorUserData($input, $output);
        $this->normalizeLessonTeachableData($input, $output);

        // report dashboard normalization
        $this->normalizeFeedbackData($input, $output);
        $this->normalizeDataForCompaniesRegisteredOnPlatform($input, $output);
        $this->normalizeDataForProfessionalsRegisteredOnPlatform($input, $output);
        $this->normalizeDataForStudentsRegisteredOnPlatform($input, $output);
        $this->normalizeDataForEducatorsRegisteredOnPlatform($input, $output);
        $this->normalizeDataForCompanyExperiences($input, $output);
        $this->normalizeDataForSchoolExperiences($input, $output);
        $this->normalizeDataForStudentParticipation($input, $output);
        $this->normalizeDataForVolunteerParticipation($input, $output);
    }

    private function normalizeExperienceData(InputInterface $input, OutputInterface $output)
    {

        $this->entityManager->clear();
        $output->writeln('Normalizing experience data.');

        $experienceUpdateCount = 0;

        foreach ($this->generateExperienceCollection() as $result) {

            /** @var Experience $experience */
            $experience           = $result[0] ?? null;
            $feedbackCount        = 0;
            $cumulativeRating     = 0;
            $npsScore             = 0;
            $cumulativePromoters  = 0;
            $cumulativeDetractors = 0;

            if (!$experience) {
                continue;
            }

            foreach ($experience->getFeedback() as $feedback) {
                $cumulativeRating += (int)$feedback->getRating();
                $feedbackCount++;

                if ($feedback->getLikelihoodToRecommendToFriend() !== null) {

                    if ($feedback->getLikelihoodToRecommendToFriend() > 8) {
                        $cumulativePromoters++;
                    }

                    if ($feedback->getLikelihoodToRecommendToFriend() < 7) {
                        $cumulativeDetractors++;
                    }
                }
            }

            if ($feedbackCount > 0) {
                $cumulativeRating = round($cumulativeRating / $feedbackCount, 1);
                $npsScore         = round((($cumulativePromoters / $feedbackCount) - ($cumulativeDetractors / $feedbackCount)) * 100);
            }

            $experience->setAverageRating($cumulativeRating);
            $experience->setNpsScore($npsScore);
            $experience->setTotalResponses($feedbackCount);

            $this->entityManager->persist($experience);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $experienceUpdateCount++;
        }

        $output->writeln('Done..... Count: ' . $experienceUpdateCount);

        return $this;
    }

    private function normalizeProfessionalUserData(InputInterface $input, OutputInterface $output)
    {

        $this->entityManager->clear();
        $output->writeln('Normalizing professional data.');

        $professionalUpdateCount = 0;

        foreach ($this->generateProfessionalCollection() as $result) {

            /** @var ProfessionalUser $professionalUser */
            $professionalUser = $result[0] ?? null;

            if (!$professionalUser) {
                continue;
            }

            $volunteerSchools = [];

            foreach ($professionalUser->getSchools() as $school) {
                $reportVolunteerSchool = new ReportVolunteerSchool();
                $reportVolunteerSchool->setSchoolName($school->getName());
                $reportVolunteerSchool->setProfessionalUser($professionalUser);
                $reportVolunteerSchool->setSchoolName($school->getName());
                $reportVolunteerSchool->setSchoolEmail($school->getEmail());
                $reportVolunteerSchool->setSchoolAddress($school->getAddress());
                $reportVolunteerSchool->setSchoolPhone($school->getPhone());
                $reportVolunteerSchool->setSchoolWebsite($school->getWebsite());
                $this->entityManager->persist($reportVolunteerSchool);
                $volunteerSchools[] = $school->getName();
            }

            foreach ($professionalUser->getRegions() as $region) {
                $reportVolunteerRegion = new ReportVolunteerRegion();
                $reportVolunteerRegion->setRegionName($region->getName());
                $reportVolunteerRegion->setProfessionalUser($professionalUser);
                $this->entityManager->persist($reportVolunteerRegion);
            }

            foreach ($professionalUser->getRolesWillingToFulfill() as $role) {
                $reportVolunteerRole = new ReportVolunteerRole();
                $reportVolunteerRole->setRoleName($role->getName());
                $reportVolunteerRole->setProfessionalUser($professionalUser);
                $this->entityManager->persist($reportVolunteerRole);
            }

            $professionalUser->setReportSchoolsVolunteerAt($volunteerSchools);

            $this->entityManager->persist($professionalUser);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $professionalUpdateCount++;
        }

        $output->writeln('Done..... Count: ' . $professionalUpdateCount);

        return $this;
    }

    private function normalizeEducatorUserData(InputInterface $input, OutputInterface $output)
    {

        $this->entityManager->clear();
        $output->writeln('Normalizing educator user data.');

        $educatorUpdateCount = 0;

        foreach ($this->generateEducatorCollection() as $result) {

            /** @var EducatorUser $educatorUser */
            $educatorUser = $result[0] ?? null;

            if (!$educatorUser) {
                continue;
            }

            if($school = $educatorUser->getSchool()) {
                $educatorUser->setReportSchool($school->getName());
            }

            $this->entityManager->persist($educatorUser);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $educatorUpdateCount++;
        }

        $output->writeln('Done..... Count: ' . $educatorUpdateCount);

        return $this;
    }

    private function normalizeLessonTeachableData(InputInterface $input, OutputInterface $output)
    {

        $this->entityManager->clear();
        $output->writeln('Normalizing lesson teachable data.');

        $count = 0;

        foreach ($this->generateLessonTeachableCollection() as $result) {

            /** @var LessonTeachable $lessonTeachable */
            $lessonTeachable = $result[0] ?? null;

            if (!$lessonTeachable) {
                continue;
            }

            if($lessonTeachable->getLesson() && $lessonTeachable->getLesson()->getTitle()) {
                $lessonTeachable->setReportLessonName($lessonTeachable->getLesson()->getTitle());
            }

            if($lessonTeachable->getUser() instanceof EducatorUser && $lessonTeachable->getLesson()) {
                $reportLessonWantTaught = new ReportLessonsWantTaught();
                $reportLessonWantTaught->setUser($lessonTeachable->getUser());
                $reportLessonWantTaught->setLesson($lessonTeachable->getLesson());
                $reportLessonWantTaught->setLessonName($lessonTeachable->getLesson()->getTitle());
                $reportLessonWantTaught->setFirstName($lessonTeachable->getUser()->getFirstName());
                $reportLessonWantTaught->setLastName($lessonTeachable->getUser()->getLastName());
                $reportLessonWantTaught->setEmail($lessonTeachable->getUser()->getEmail());
                $this->entityManager->persist($reportLessonWantTaught);
            }

            if($lessonTeachable->getUser() instanceof ProfessionalUser && $lessonTeachable->getLesson()) {
                $reportLessonCanTeach = new ReportLessonsCanTeach();
                $reportLessonCanTeach->setUser($lessonTeachable->getUser());
                $reportLessonCanTeach->setLesson($lessonTeachable->getLesson());
                $reportLessonCanTeach->setLessonName($lessonTeachable->getLesson()->getTitle());
                $reportLessonCanTeach->setFirstName($lessonTeachable->getUser()->getFirstName());
                $reportLessonCanTeach->setLastName($lessonTeachable->getUser()->getLastName());
                $reportLessonCanTeach->setEmail($lessonTeachable->getUser()->getEmail());
                $this->entityManager->persist($reportLessonCanTeach);
            }

            $this->entityManager->persist($lessonTeachable);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $count++;
        }

        $output->writeln('Done..... Count: ' . $count);

        return $this;
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
                $report->setReportType(Report::TYPE_DASHBOARD);
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
                $report->setReportType(Report::TYPE_DASHBOARD);
                $report->setDashboardType('professionals_registered_on_platform');
                $report->setCompanyName($professional->getCompany() ? $professional->getCompany()->getName() : null);
                $report->setCompany($professional->getCompany() ? $professional->getCompany()->getId() : null);
                $report->setRegistrationDate($professional->getCreatedAt());
                $report->setProfessional($professional->getId());
                $report->setProfessionalName($professional->getFullName());
                $report->setProfessionalFirstName($professional->getFirstName());
                $report->setProfessionalLastName($professional->getLastName());

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
                $report->setReportType(Report::TYPE_DASHBOARD);
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
                $report->setReportType(Report::TYPE_DASHBOARD);
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

                $regionIds   = [];
                $regionNames = [];
                $schoolIds   = [];
                $schoolNames = [];

                /** @var CompanyExperience $companyExperience */
                $companyExperience = $result[0] ?? null;

                if (!$companyExperience) {
                    continue;
                }

                $report = new Report();
                $report->setReportType(Report::TYPE_DASHBOARD);
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

                foreach ($companyExperience->getRegistrations() as $registration) {

                    if (!$user = $registration->getUser()) {
                        continue;
                    }

                    if ($user instanceof StudentUser || $user instanceof EducatorUser) {

                        if ($school = $user->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();
                            $report->setSchool($school->getId());
                            $report->setSchoolName($school->getName());

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                                $report->setRegion($region->getId());
                                $report->setRegionName($region->getName());
                            }
                        }
                    }
                }

                // todo it looks like each registration needs it's own report row and not just the entire company experience.
                //  and since we are going off of participation, I believe that we need to have a new row for each report and
                //  need a single scalar school and single scalar region for each participation one


                // todo the company experiences and school experiences don't go off of registrations so we need to split the participation reports apart from the company and
                // todo school experience reports.


                // todo how does this one work: https://share.getcloudapp.com/bLuALKy0

                // todo we can push the registration count as done and wait on the participation and company/school experience dashboards.

                // todo I'm going to remove participationType. This is messing shit up trying to merge all of these reports. That's what the problem is, cause we are dealing with different
                //  data aggregates.

                $report->setRegionNames($regionNames);
                $report->setRegions($regionIds);
                $report->setSchools($schoolIds);
                $report->setSchoolNames($schoolNames);

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
                $report->setReportType(Report::TYPE_DASHBOARD);
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

            foreach ($this->generateRegistrationCollection() as $result) {

                /** @var Registration $registration */
                $registration = $result[0] ?? null;

                if (!$registration) {
                    continue;
                }

                if (!$experience = $registration->getExperience()) {
                    continue;
                }

                if (!$user = $registration->getUser()) {
                    continue;
                }

                if (!$user instanceof StudentUser) {
                    continue;
                }

                $schoolIds    = [];
                $schoolNames  = [];
                $regionIds    = [];
                $regionNames  = [];
                $companyIds   = [];
                $companyNames = [];

                $report = new Report();
                $report->setReportType(Report::TYPE_DASHBOARD);
                $report->setDashboardType('student_experience_participation');
                $report->setExperienceStartDate($experience->getStartDateAndTime());
                $report->setExperienceName($experience->getTitle());
                $report->setExperience($experience->getId());
                $report->setStudent($user->getId());
                $report->setStudentName($user->getFullName());
                $report->setStudentFirstName($user->getFirstName());
                $report->setStudentLastName($user->getLastName());
                $report->setRegistrationDate($registration->getCreatedAt());
                $report->setRegistration($registration->getId());

                if ($school = $user->getSchool()) {
                    $schoolIds[]   = $school->getId();
                    $schoolNames[] = $school->getName();
                    $report->setSchool($school->getId());
                    $report->setSchoolName($school->getName());

                    if ($region = $school->getRegion()) {
                        $regionIds[]   = $region->getId();
                        $regionNames[] = $region->getName();
                        $report->setRegion($region->getId());
                        $report->setRegionName($region->getName());
                    }
                }

                if ($type = $experience->getType()) {
                    $report->setExperienceType($experience->getType()->getName());
                    $report->setExperienceTypeId($experience->getType()->getId());
                }

                if ($experience instanceof SchoolExperience) {
                    $report->setExperienceClass(SchoolExperience::class);
                }

                if ($experience instanceof CompanyExperience) {

                    if ($company = $experience->getCompany()) {
                        $companyIds[]   = $company->getId();
                        $companyNames[] = $company->getName();
                        $report->setCompany($company->getId());
                        $report->setCompanyName($company->getName());
                    }

                    $report->setExperienceClass(CompanyExperience::class);
                }

                if ($experience instanceof StudentToMeetProfessionalExperience) {

                    if (($originalRequest = $experience->getOriginalRequest()) && $professional = $originalRequest->getProfessional()) {

                        if ($company = $professional->getCompany()) {
                            $companyIds[]   = $company->getId();
                            $companyNames[] = $company->getName();
                            $report->setCompany($company->getId());
                            $report->setCompanyName($company->getName());
                        }
                    }

                    $report->setExperienceType('Informational Interviewer');
                    $report->setExperienceClass(StudentToMeetProfessionalExperience::class);
                }

                if ($experience instanceof TeachLessonExperience) {

                    if (($professional = $experience->getTeacher())) {

                        if ($company = $professional->getCompany()) {
                            $companyIds[]   = $company->getId();
                            $companyNames[] = $company->getName();
                            $report->setCompany($company->getId());
                            $report->setCompanyName($company->getName());
                        }
                    }

                    $report->setExperienceType('Guest Topic Instructor');
                    $report->setExperienceClass(TeachLessonExperience::class);
                }

                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegionNames($regionNames);
                $report->setRegions($regionIds);
                $report->setCompanies($companyIds);
                $report->setCompanyNames($companyNames);

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

            foreach ($this->generateRegistrationCollection() as $result) {

                /** @var Registration $registration */
                $registration = $result[0] ?? null;

                if (!$registration) {
                    continue;
                }

                if (!$experience = $registration->getExperience()) {
                    continue;
                }

                if (!$user = $registration->getUser()) {
                    continue;
                }

                if (!$user instanceof ProfessionalUser) {
                    continue;
                }

                $schoolIds    = [];
                $schoolNames  = [];
                $regionIds    = [];
                $regionNames  = [];
                $companyIds   = [];
                $companyNames = [];

                $report = new Report();
                $report->setReportType(Report::TYPE_DASHBOARD);
                $report->setDashboardType('professional_volunteer_participation');
                $report->setExperienceStartDate($experience->getStartDateAndTime());
                $report->setExperienceName($experience->getTitle());
                $report->setExperience($experience->getId());
                $report->setProfessional($user->getId());
                $report->setProfessionalName($user->getFullName());
                $report->setRegistrationDate($registration->getCreatedAt());
                $report->setRegistration($registration->getId());

                if ($type = $experience->getType()) {
                    $report->setExperienceType($experience->getType()->getName());
                    $report->setExperienceTypeId($experience->getType()->getId());
                }

                if ($experience instanceof SchoolExperience) {

                    if ($school = $experience->getSchool()) {
                        $schoolIds[]   = $school->getId();
                        $schoolNames[] = $school->getName();
                        $report->setSchool($school->getId());
                        $report->setSchoolName($school->getName());

                        if ($region = $school->getRegion()) {
                            $regionIds[]   = $region->getId();
                            $regionNames[] = $region->getName();
                            $report->setRegion($region->getId());
                            $report->setRegionName($region->getName());
                        }
                    }
                }

                if ($experience instanceof CompanyExperience) {

                    if ($company = $experience->getCompany()) {
                        $companyIds[]   = $company->getId();
                        $companyNames[] = $company->getName();
                        $report->setCompany($company->getId());
                        $report->setCompanyName($company->getName());
                    }
                }

                if ($experience instanceof StudentToMeetProfessionalExperience) {

                    if (($originalRequest = $experience->getOriginalRequest()) && $professional = $originalRequest->getProfessional()) {

                        if ($company = $professional->getCompany()) {
                            $companyIds[]   = $company->getId();
                            $companyNames[] = $company->getName();
                            $report->setCompany($company->getId());
                            $report->setCompanyName($company->getName());
                        }

                        if (($student = $originalRequest->getStudent()) && $school = $student->getSchool()) {
                            $schoolIds[]   = $school->getId();
                            $schoolNames[] = $school->getName();
                            $report->setSchool($school->getId());
                            $report->setSchoolName($school->getName());

                            if ($region = $school->getRegion()) {
                                $regionIds[]   = $region->getId();
                                $regionNames[] = $region->getName();
                                $report->setRegion($region->getId());
                                $report->setRegionName($region->getName());
                            }
                        }
                    }

                    $report->setExperienceType('Informational Interviewer');
                }

                if ($experience instanceof TeachLessonExperience) {

                    if ($school = $experience->getSchool()) {
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

                    if (($professional = $experience->getTeacher())) {

                        if ($company = $professional->getCompany()) {
                            $companyIds[]   = $company->getId();
                            $companyNames[] = $company->getName();
                            $report->setCompany($company->getId());
                            $report->setCompanyName($company->getName());
                        }
                    }

                    $report->setExperienceType('Guest Topic Instructor');
                }


                $report->setSchoolNames($schoolNames);
                $report->setSchools($schoolIds);
                $report->setRegionNames($regionNames);
                $report->setRegions($regionIds);
                $report->setCompanies($companyIds);
                $report->setCompanyNames($companyNames);

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

    private function addCompanyAdministratorRoles(InputInterface $input, OutputInterface $output) {

        $output->writeln('Adding roles for company administrators.');

        $updateCount = 0;

        foreach ($this->generateCompanyCollection() as $result) {

            /** @var Company $company */
            $company = $result[0] ?? null;

            if (!$company) {
                continue;
            }

            /** @var User $owner */
            if(!$owner = $company->getOwner()) {
                continue;
            }

            $owner->addRole(User::ROLE_COMPANY_ADMINISTRATOR);

            $this->entityManager->persist($owner);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $updateCount++;
        }

        $output->writeln('Done with adding company administrator roles. Count: ' . $updateCount);

        return $this;
    }

    /**
     * @return iterable
     */
    private function generateExperienceCollection(): iterable
    {
        $queryBuilder = $this->experienceRepository->createQueryBuilder('e')->getQuery();

        /** @var Experience $experience */
        foreach ($queryBuilder->iterate() as $experience) {

            yield $experience;
        }
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
    private function generateLessonTeachableCollection(): iterable
    {
        $queryBuilder = $this->lessonTeachableRepository->createQueryBuilder('lt')->getQuery();

        /** @var LessonTeachable $lessonTeachable */
        foreach ($queryBuilder->iterate() as $lessonTeachable) {

            yield $lessonTeachable;
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

    /**
     * @return iterable
     */
    private function generateRegistrationCollection(): iterable
    {
        $queryBuilder = $this->registrationRepository->createQueryBuilder('r')->getQuery();

        /** @var Registration $registration */
        foreach ($queryBuilder->iterate() as $registration) {

            yield $registration;
        }
    }
}