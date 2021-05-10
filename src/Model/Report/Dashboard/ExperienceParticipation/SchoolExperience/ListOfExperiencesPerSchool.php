<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\SchoolExperience;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfExperiencesPerSchool extends AbstractDashboard
{
    protected $header = 'School experience count';

    protected $subHeader = '';

    protected $position = 5;

    protected $schools = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if(empty($feedback['dashboardType'])) {
                continue;
            }

            if ($feedback['dashboardType'] !== 'school_experience_participation') {
                continue;
            }

            if (empty($feedback['school'])) {
                continue;
            }

            if (empty($feedback['schoolName'])) {
                continue;
            }

            if (empty($feedback['experience'])) {
                continue;
            }

            if(empty($this->schools[$feedback['school']]['experience_count'])) {
                $this->schools[$feedback['school']]['experience_count'] = 0;
            }

            $this->schools[$feedback['school']]['experience_count']++;

            $this->schools[$feedback['school']]['name'] = $feedback['schoolName'];
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
        return 'report/dashboard/experience_participation/school_experience/list_of_experiences_per_school.html.twig';
    }

    public function getLocation()
    {
        return 'bottom';
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getSchools(): array
    {
        return $this->schools;
    }
}