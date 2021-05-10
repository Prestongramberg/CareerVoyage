<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\Student\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class NumberOfExperiencesByStudents extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = [];

    protected $label = 'Bar Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = 'Number of experiences by students';

    protected $subHeader = '';

    protected $position = 9;

    protected $footer = '';

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $experiences = [];
        $data = [];
        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if(empty($feedback['dashboardType'])) {
                continue;
            }

            if ($feedback['dashboardType'] !== 'student_experience_participation') {
                continue;
            }

            if (empty($feedback['experience'])) {
                continue;
            }

            if (empty($feedback['schoolName'])) {
                continue;
            }

            if (empty($feedback['school'])) {
                continue;
            }

            if(empty($data[$feedback['schoolName']])) {
                $data[$feedback['schoolName']] = [];
            }

            if(empty($data[$feedback['schoolName']][$feedback['experience']])) {
                $data[$feedback['schoolName']][$feedback['experience']] = true;
            }

            $experiences[] = $feedback['experience'];
        }

        $this->subHeader = sprintf("Total: %s", count(array_unique($experiences)));

        foreach($data as $schoolName => $experiences) {
            $this->labels[] = $schoolName;
            $this->data[] = count($experiences);
        }

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
                                'beginAtZero' => true
                            ]
                        ]
                    ]
                ]
            ],
            'data' => [
                'labels' => $this->labels,
                'datasets' => [
                    [
                        'backgroundColor' => $this->backgroundColor,
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

    public function getFooter()
    {
        return $this->footer;
    }

    public function getTemplate()
    {
        return 'report/dashboard/bar_chart.html.twig';
    }

    public function getLocation()
    {
        return 'full-bottom';
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