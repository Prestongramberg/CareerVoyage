<?php

namespace App\Model\Report\Dashboard\RegistrationSummary;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfCompanies extends AbstractDashboard
{
    protected $header = 'Companies Registered';

    protected $subHeader = '';

    protected $position = 1;

    protected $companies = [];

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

            if($feedback['dashboardType'] !== 'companies_registered_on_platform') {
                continue;
            }

            if (empty($feedback['companyName'])) {
                continue;
            }

            if (empty($feedback['company'])) {
                continue;
            }

            $this->companies[$feedback['company']] = [
                'name' => $feedback['companyName']
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
        return 'report/dashboard/registration_summary/list_of_companies.html.twig';
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