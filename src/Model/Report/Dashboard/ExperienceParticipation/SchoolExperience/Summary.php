<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\SchoolExperience;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use App\Repository\CompanyRepository;
use Pinq\ITraversable;
use Pinq\Traversable;

class Summary extends AbstractDashboard
{
    protected $header = 'Summary';

    protected $subHeader = '';

    protected $position = 0;

    protected $totalSchoolExperiences = 0;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     * @param             $totalCompanyExperiences
     */
    public function __construct(Traversable $feedbackCollection, $totalSchoolExperiences, $hasFilters)
    {
        $experiences = [];

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['dashboardType'])) {
                continue;
            }

            if (empty($feedback['experience'])) {
                continue;
            }

            $experiences[] = $feedback['experience'];
        }

        if ($hasFilters) {
            $this->totalSchoolExperiences = count(array_unique($experiences));
        } else {
            $this->totalSchoolExperiences = $totalSchoolExperiences;
        }
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
        return 'report/dashboard/experience_participation/school_experience/summary.html.twig';
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

    public function getTotalSchoolExperiences(): int
    {
        return $this->totalSchoolExperiences;
    }
}