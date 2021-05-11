<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\CompanyExperience;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfExperiencesPerCompany extends AbstractDashboard
{
    protected $header = 'Company experience count';

    protected $subHeader = '';

    protected $position = 2;

    protected $companies = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['company'])) {
                continue;
            }

            if (empty($feedback['companyName'])) {
                continue;
            }

            if (empty($feedback['experience'])) {
                continue;
            }

            if(empty($this->companies[$feedback['company']]['experience_count'])) {
                $this->companies[$feedback['company']]['experience_count'] = 0;
            }

            $this->companies[$feedback['company']]['experience_count']++;

            $this->companies[$feedback['company']]['name'] = $feedback['companyName'];
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
        return 'report/dashboard/experience_participation/company_experience/list_of_experiences_per_company.html.twig';
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

    /**
     * @return array
     */
    public function getCompanies(): array
    {
        return $this->companies;
    }
}