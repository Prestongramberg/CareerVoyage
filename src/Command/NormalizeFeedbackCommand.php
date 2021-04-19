<?php

namespace App\Command;

use App\Cache\CacheKey;
use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\Feedback;
use App\Entity\ProfessionalReviewCompanyExperienceFeedback;
use App\Entity\ProfessionalReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalUser;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentReviewSchoolExperienceFeedback;
use App\Repository\FeedbackRepository;
use App\Repository\ProfessionalUserRepository;
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
     * @var string
     */
    private $cacheDirectory;

    /**
     * NormalizeFeedbackCommand constructor.
     *
     * @param EntityManagerInterface     $entityManager
     * @param FeedbackRepository         $feedbackRepository
     * @param ProfessionalUserRepository $professionalUserRepository
     * @param SerializerInterface        $serializer
     * @param                            $cacheDirectory
     */
    public function __construct(EntityManagerInterface $entityManager, FeedbackRepository $feedbackRepository,
                                ProfessionalUserRepository $professionalUserRepository, SerializerInterface $serializer,
                                $cacheDirectory
    ) {
        $this->entityManager              = $entityManager;
        $this->feedbackRepository         = $feedbackRepository;
        $this->professionalUserRepository = $professionalUserRepository;
        $this->serializer                 = $serializer;
        $this->cacheDirectory             = $cacheDirectory;

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
        $feedbackCollection = $this->generateFeedbackCollection();

        $feedbackUpdateCount = 0;
        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

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

                    /*          if ($feedback->getCompanyExperience() && $state = $feedback->getCompanyExperience()->getState()) {
                                  foreach ($state->getSchools() as $school) {
                                      $schoolIds[]   = $school->getId();
                                      $schoolNames[] = $school->getName();
                                  }
                              }*/

                    /*     if ($feedback->getProfessional()) {

                             foreach ($feedback->getProfessional()->getRegions() as $region) {
                                 $regionIds[]   = $region->getId();
                                 $regionNames[] = $region->getName();
                             }

                             foreach ($feedback->getProfessional()->getSchools() as $school) {
                                 $schoolIds[]   = $school->getId();
                                 $schoolNames[] = $school->getName();
                             }
                         }*/

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

                    /*   if ($feedback->getProfessional()) {

                           foreach ($feedback->getProfessional()->getRegions() as $region) {
                               $regionIds[]   = $region->getId();
                               $regionNames[] = $region->getName();
                           }

                           foreach ($feedback->getProfessional()->getSchools() as $school) {
                               $schoolIds[]   = $school->getId();
                               $schoolNames[] = $school->getName();
                           }
                       }*/


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

                    $feedbackUpdateCount++;
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

            if ($feedbackUpdateCount % 10 === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        // todo add cache delete here

        $cache = new FilesystemAdapter('feedback', 0, $this->cacheDirectory . '/pintex');

        $cache->delete(CacheKey::FEEDBACK);

        $cache->get(CacheKey::FEEDBACK, function (ItemInterface $item) {

            $cachedFeedback = [];
            foreach ($this->generateFeedbackCollection() as $feedback) {
                $data             = $this->serializer->serialize($feedback, 'json', ['groups' => ['FEEDBACK']]);
                $data             = json_decode($data, true);
                $cachedFeedback[] = $data;
            }

            return $cachedFeedback;
        });

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