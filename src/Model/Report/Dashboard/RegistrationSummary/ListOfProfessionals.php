<?php

namespace App\Model\Report\Dashboard\RegistrationSummary;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfProfessionals extends AbstractDashboard
{
    protected $header = 'Professionals Registered';

    protected $subHeader = '';

    protected $position = 3;

    protected $professionals = [];

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

            if($feedback['dashboardType'] !== 'professionals_registered_on_platform') {
                continue;
            }
            
            if (empty($feedback['professionalName'])) {
                continue;
            }

            if (empty($feedback['professional'])) {
                continue;
            }

            $this->professionals[$feedback['professional']] = [
                'name' => $feedback['professionalName'],
                'company' => $feedback['companyName'] ?? 'N/A'
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
        return 'report/dashboard/registration_summary/list_of_professionals.html.twig';
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