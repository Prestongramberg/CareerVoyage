<?php

namespace App\Model\Report\Dashboard\Feedback\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ExperienceRating extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = ['1', '2', '3', '4', '5'];

    protected $label = 'Bar Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = '';

    protected $subHeader = '';

    protected $position = 0;

    protected $footer = '1: Poor < 3: Average > 5: Excellent';

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $data             = [];
        $totalResponses   = 0;
        $cumulativeRating = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            $feedbackRating = $feedback['rating'] ?? null;

            if (!$feedbackRating) {
                continue;
            }

            $totalResponses++;

            $cumulativeRating += (int)$feedbackRating;

            if (!isset($data[$feedbackRating])) {
                $data[$feedbackRating] = 0;
            }

            $data[$feedbackRating]++;
        }

        foreach ($this->labels as $label) {
            $this->data[] = isset($data[$label]) ? $data[$label] : 0;
        }

        $this->header = $this->label = 'Experience Rating';

        if ($totalResponses !== 0) {
            $this->subHeader = sprintf("Average: %s", round($cumulativeRating / $totalResponses, 1));
        } else {
            $this->subHeader = sprintf("(%s Responses)", $totalResponses);
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
                                'beginAtZero' => true,
                                'precision' => 0,
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
}