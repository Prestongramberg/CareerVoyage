<?php

namespace App\Model\Report\Dashboard\RegistrationSummary;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfEducators extends AbstractDashboard
{
    protected $header = 'Educators Registered';

    protected $subHeader = '';

    protected $position = 5;

    protected $educators = [];

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

            if ($feedback['dashboardType'] !== 'educators_registered_on_platform') {
                continue;
            }

            if (empty($feedback['educatorName'])) {
                continue;
            }

            if (empty($feedback['educator'])) {
                continue;
            }

            if (empty($feedback['school'])) {
                continue;
            }

            if (empty($feedback['schoolName'])) {
                continue;
            }

            $this->educators[$feedback['educator']] = [
                'name' => $feedback['educatorName'],
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
        return 'report/dashboard/registration_summary/list_of_educators.html.twig';
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

    public function getEducators(): array
    {
        return $this->educators;
    }
}