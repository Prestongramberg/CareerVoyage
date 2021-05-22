<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\Student\LineChart;

use App\Entity\Feedback;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class TotalRegisteredStudents extends AbstractDashboard
{
    protected $type = 'line';

    protected $labels = [];

    protected $label = 'Line Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = 'Student experience participation per month';

    protected $subHeader = '';

    protected $footer = '';

    protected $position = 2;

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

            if(empty($feedback['registrationDate'])) {
                continue;
            }

            if(empty($feedback['registration'])) {
                continue;
            }

            $registrationDate = new \DateTime($feedback['registrationDate']);
            $registrationDate = $registrationDate->format('F Y');

            if (!isset($this->data[$registrationDate])) {
                $this->data[$registrationDate] = 0;
            }

            $this->data[$registrationDate]++;
        }

        $this->labels = array_keys($this->data);
        $this->data = array_values($this->data);
    }

    public function render()
    {
        return json_encode([
            'type' => $this->type,
            'options' => [
                'legend' => [
                    'display' => false
                ],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'beginAtZero' => true,
                                'precision' => 0
                            ]
                        ]
                    ]
                ]
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