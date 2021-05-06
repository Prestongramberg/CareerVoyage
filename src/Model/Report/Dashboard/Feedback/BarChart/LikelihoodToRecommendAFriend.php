<?php

namespace App\Model\Report\Dashboard\Feedback\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class LikelihoodToRecommendAFriend extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    protected $label = 'Bar Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = '';

    protected $subHeader = '';

    protected $footer = '';

    protected $position = 5;

    protected $average = 0;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $data           = [];
        $totalResponses = 0;
        $cumulative     = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if ($feedback['feedbackProvider'] !== 'Student') {
                continue;
            }

            if ($feedback['likelihoodToRecommendToFriend'] === null) {
                continue;
            }

            $totalResponses++;

            $likelihood = $feedback['likelihoodToRecommendToFriend'];

            $cumulative += (int)$likelihood;

            if (!isset($data[$likelihood])) {
                $data[$likelihood] = 0;
            }

            $data[$likelihood]++;
        }

        foreach ($this->labels as $label) {
            $this->data[] = isset($data[$label]) ? $data[$label] : 0;
        }

        if($totalResponses !== 0) {
            $this->average = sprintf("Average: %s", round($cumulative/ $totalResponses, 1));
        }

        $this->header = "Likelihood to recommend to a friend";

        $this->subHeader = sprintf("(%s Responses)", $totalResponses);
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

    /**
     * @return string
     */
    public function getFooter(): string
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

    public function getAverage()
    {
        return $this->average;
    }

    public function setAverage($average)
    {
        $this->average = $average;
    }
}