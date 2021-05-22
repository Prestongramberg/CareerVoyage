<?php

namespace App\Model\Report\Dashboard\RegistrationSummary;

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

    protected $totalNumberCompaniesRegistered = 0;
    protected $totalNumberProfessionalsRegistered = 0;
    protected $totalNumberEducatorsRegistered = 0;
    protected $totalNumberStudentsRegistered = 0;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if(empty($feedback['dashboardType'])) {
                continue;
            }

            if($feedback['dashboardType'] === 'companies_registered_on_platform') {
                if (!empty($feedback['companyName']) && !empty($feedback['company'])) {
                    $this->totalNumberCompaniesRegistered++;
                }
            }

            if($feedback['dashboardType'] === 'professionals_registered_on_platform') {
                if (!empty($feedback['professionalName']) && !empty($feedback['professional'])) {
                    $this->totalNumberProfessionalsRegistered++;
                }
            }

            if($feedback['dashboardType'] === 'educators_registered_on_platform') {
                if (!empty($feedback['educatorName']) && !empty($feedback['educator'])) {
                    $this->totalNumberEducatorsRegistered++;
                }
            }

            if($feedback['dashboardType'] === 'students_registered_on_platform') {
                if (!empty($feedback['studentName']) && !empty($feedback['student'])) {
                    $this->totalNumberStudentsRegistered++;
                }
            }

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
        return 'report/dashboard/registration_summary/summary.html.twig';
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

    public function getTotalNumberCompaniesRegistered(): int
    {
        return $this->totalNumberCompaniesRegistered;
    }

    public function getTotalNumberProfessionalsRegistered(): int
    {
        return $this->totalNumberProfessionalsRegistered;
    }

    public function getTotalNumberEducatorsRegistered(): int
    {
        return $this->totalNumberEducatorsRegistered;
    }

    public function getTotalNumberStudentsRegistered(): int
    {
        return $this->totalNumberStudentsRegistered;
    }
}