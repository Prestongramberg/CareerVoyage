<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\SchoolExperience;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfExperiencesPerType extends AbstractDashboard
{
    protected $header = 'School experiences per type';

    protected $subHeader = '';

    protected $position = 6;

    protected $experiences = [];

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

            if (empty($feedback['experienceTypeId'])) {
                continue;
            }

            if (empty($feedback['experienceType'])) {
                continue;
            }

            if(empty($this->experiences[$feedback['experienceTypeId']]['experience_count'])) {
                $this->experiences[$feedback['experienceTypeId']]['experience_count'] = 0;
            }

            $this->experiences[$feedback['experienceTypeId']]['experience_count']++;

            $this->experiences[$feedback['experienceTypeId']]['type'] = $feedback['experienceType'];
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
        return 'report/dashboard/experience_participation/school_experience/list_of_experiences_per_type.html.twig';
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

    public function getExperiences(): array
    {
        return $this->experiences;
    }
}