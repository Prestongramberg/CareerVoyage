<?php

namespace App\Model\Report\Dashboard\Feedback\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;

class PromoterNeutralDetractor extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = ['Promoter', 'Neutral', 'Detractor'];

    protected $label = '';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = 'Count of Promoters, Passives and Detractors.';

    protected $subHeader = '';

    protected $footer = '';

    protected $position = 6;

    /**
     * BarChart constructor.
     *
     * @param FeedbackCollection $feedbackCollection
     */
    public function __construct(FeedbackCollection $feedbackCollection)
    {
        $cumulativePromoters  = 0;
        $cumulativeDetractors = 0;
        $cumulativePassives = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if ($feedback->getLikelihoodToRecommendToFriend() > 8) {
                $cumulativePromoters++;
            }

            if ($feedback->getLikelihoodToRecommendToFriend() < 7) {
                $cumulativeDetractors++;
            }

            if(!$feedback->getLikelihoodToRecommendToFriend()) {
                $cumulativePassives++;
            }
        }

        $this->data = [$cumulativePromoters, $cumulativePassives, $cumulativeDetractors];

    }

    public function render()
    {
        return json_encode([
            'type' => $this->type,
            'options' => [
                'indexAxis' => 'y',
            ],
            'data' => [
                'labels' => $this->labels,
                'datasets' => [
                    [
                        'backgroundColor' => $this->backgroundColor,
                        'borderColor' => $this->borderColor,
                        'label' => $this->label,
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