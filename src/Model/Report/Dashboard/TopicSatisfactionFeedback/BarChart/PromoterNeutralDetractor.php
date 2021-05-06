<?php

namespace App\Model\Report\Dashboard\TopicSatisfactionFeedback\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class PromoterNeutralDetractor extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = ['Promoter', 'Neutral', 'Detractor'];

    protected $label = '';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = 'Count of Promoters (9-10), Passives (7-8), Detractors (0-6).';

    protected $subHeader = '';

    protected $footer = '';

    protected $position = 6;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $cumulativePromoters  = 0;
        $cumulativeDetractors = 0;
        $cumulativePassives = 0;
        $totalResponses = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if ($feedback['feedbackProvider'] !== 'Student') {
                continue;
            }

            if($feedback['likelihoodToRecommendToFriend'] === null) {
                continue;
            }

            if ($feedback['likelihoodToRecommendToFriend'] > 8) {
                $cumulativePromoters++;
            }

            if ($feedback['likelihoodToRecommendToFriend'] < 7) {
                $cumulativeDetractors++;
            }

            if ($feedback['likelihoodToRecommendToFriend'] === 7 || $feedback['likelihoodToRecommendToFriend'] === 8) {
                $cumulativePassives++;
            }

            $totalResponses++;
        }

        $this->data = [$cumulativePromoters, $cumulativePassives, $cumulativeDetractors];

        $this->subHeader = sprintf("(%s Responses)", $totalResponses);

    }

    public function render()
    {
        return json_encode([
            'type' => $this->type,
            'options' => [
                'indexAxis' => 'y',
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
}