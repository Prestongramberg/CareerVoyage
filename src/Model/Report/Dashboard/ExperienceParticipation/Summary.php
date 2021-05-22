<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class Summary extends AbstractDashboard
{
    protected $header = 'Summary';

    protected $subHeader = '';

    protected $position = 0;

    protected $totalCompanyExperiences = 0;
    protected $totalSchoolExperiences = 0;
    protected $totalStudentsRegistered = 0;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $companies = [];
        $schools   = [];
        $students  = [];

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['dashboardType'])) {
                continue;
            }

            if (empty($feedback['dashboardType'])) {
                continue;
            }

            if ($feedback['dashboardType'] === 'company_experience_participation' && !empty($feedback['company'])) {
                $companies[] = $feedback['company'];
            }

            if ($feedback['dashboardType'] === 'school_experience_participation' && !empty($feedback['school'])) {
                $schools[] = $feedback['school'];
            }

            if ($feedback['dashboardType'] === 'student_experience_participation' && !empty($feedback['student']) && !empty($feedback['school']) && !empty($feedback['schoolName'])) {
                $students[] = $feedback['student'];
            }
        }

        $this->totalCompanyExperiences = count(array_unique($companies));
        $this->totalSchoolExperiences = count(array_unique($schools));
        $this->totalStudentsRegistered =  count(array_unique($students));
    }

    public function render()
    {
        return json_encode([]);
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getSubHeader(): string
    {
        return $this->subHeader;
    }

    public function getFooter()
    {
        return '';
    }

    public function getTemplate()
    {
        return 'report/dashboard/experience_participation/summary.html.twig';
    }

    public function getLocation()
    {
        return 'top';
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getTotalCompanyExperiences(): int
    {
        return $this->totalCompanyExperiences;
    }

    public function getAverageExperiencesPerCompany()
    {
        return $this->averageExperiencesPerCompany;
    }

    public function getTotalSchoolExperiences(): int
    {
        return $this->totalSchoolExperiences;
    }

    public function getAverageExperiencesPerSchool()
    {
        return $this->averageExperiencesPerSchool;
    }

    public function getTotalStudentsRegistered(): int
    {
        return $this->totalStudentsRegistered;
    }
}