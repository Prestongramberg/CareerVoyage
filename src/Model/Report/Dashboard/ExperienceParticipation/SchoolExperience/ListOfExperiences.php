<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\SchoolExperience;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfExperiences extends AbstractDashboard
{
    protected $header = 'School experiences';

    protected $subHeader = '';

    protected $position = 4;

    protected $experiences = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['experienceName'])) {
                continue;
            }

            if (empty($feedback['experienceType'])) {
                continue;
            }

            if (empty($feedback['schoolName'])) {
                continue;
            }

            if (empty($feedback['experience'])) {
                continue;
            }

            $this->experiences[$feedback['experience']] = [
                'title' => $feedback['experienceName'],
                'type' => $feedback['experienceType'],
                'school' => $feedback['schoolName'],
            ];
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
        return 'report/dashboard/experience_participation/school_experience/list_of_experiences.html.twig';
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