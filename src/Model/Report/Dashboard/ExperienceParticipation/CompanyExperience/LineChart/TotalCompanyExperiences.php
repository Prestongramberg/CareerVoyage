<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\CompanyExperience\LineChart;

use App\Entity\Feedback;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class TotalCompanyExperiences extends AbstractDashboard
{
    protected $type = 'line';

    protected $labels = [];

    protected $label = 'Line Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = 'Total company experiences';

    protected $subHeader = '';

    protected $footer = '';

    protected $position = 1;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     *
     * @throws \Exception
     */
    public function __construct(Traversable $feedbackCollection)
    {
        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['experienceStartDate'])) {
                continue;
            }

            if (empty($feedback['dashboardType'])) {
                continue;
            }

            if ($feedback['dashboardType'] !== 'company_experience_participation') {
                continue;
            }

            $experienceStartDate = new \DateTime($feedback['experienceStartDate']);
            $experienceStartDate = $experienceStartDate->format('F Y');

            if (!isset($this->data[$experienceStartDate])) {
                $this->data[$experienceStartDate] = 0;
            }

            $this->data[$experienceStartDate]++;
        }

        $this->labels = array_keys($this->data);
        $this->data   = array_values($this->data);
    }

    public function render()
    {
        return json_encode([
            'type' => $this->type,
            'options' => [
                'legend' => [
                    'display' => false,
                ],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'beginAtZero' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'data' => [
                'labels' => $this->labels,
                'datasets' => [
                    [
                        'borderColor' => $this->borderColor,
                        'data' => $this->data,
                    ],
                ],
            ],
        ]);
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

    /**
     * @return string
     */
    public function getFooter(): string
    {
        return $this->footer;
    }

    public function getTemplate()
    {
        return 'report/dashboard/line_chart.html.twig';
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
}