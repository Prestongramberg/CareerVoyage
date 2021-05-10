<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\CompanyExperience;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfExperiences extends AbstractDashboard
{
    protected $header = 'Company experiences';

    protected $subHeader = '';

    protected $position = 1;

    protected $experiences = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['dashboardType'])) {
                continue;
            }

            if ($feedback['dashboardType'] !== 'company_experience_participation') {
                continue;
            }

            if (empty($feedback['experienceName'])) {
                continue;
            }

            if (empty($feedback['experienceType'])) {
                continue;
            }

            if (empty($feedback['companyName'])) {
                continue;
            }

            if (empty($feedback['experience'])) {
                continue;
            }

            $this->experiences[$feedback['experience']] = [
                'title' => $feedback['experienceName'],
                'type' => $feedback['experienceType'],
                'company' => $feedback['companyName'],
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
        return 'report/dashboard/experience_participation/company_experience/list_of_experiences.html.twig';
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