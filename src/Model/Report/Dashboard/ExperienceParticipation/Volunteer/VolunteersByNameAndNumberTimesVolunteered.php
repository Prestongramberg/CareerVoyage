<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\Volunteer;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class VolunteersByNameAndNumberTimesVolunteered extends AbstractDashboard
{
    protected $header = 'Volunteers by name and # of times volunteered';

    protected $subHeader = '';

    protected $position = 13;

    protected $professionals = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['professional'])) {
                continue;
            }

            if (empty($feedback['professionalName'])) {
                continue;
            }

            if(empty($this->professionals[$feedback['professional']]['experience_count'])) {
                $this->professionals[$feedback['professional']]['experience_count'] = 0;
            }

            $this->professionals[$feedback['professional']]['experience_count']++;

            $this->professionals[$feedback['professional']]['name'] = $feedback['professionalName'];
            $this->professionals[$feedback['professional']]['company'] = $feedback['companyName'] ?? 'No company Affiliation';
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
        return 'report/dashboard/experience_participation/volunteer/volunteer_by_name_and_number_of_times.html.twig';
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

    public function getProfessionals(): array
    {
        return $this->professionals;
    }
}