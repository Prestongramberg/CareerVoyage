<?php

namespace App\Model\Report\Dashboard\Feedback\PieChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;

class LearnedSomethingNew extends AbstractDashboard
{
    protected $type = 'pie';

    protected $labels = ['Yes', 'No'];

    protected $label = 'Pie Chart';

    protected $data = [0, 0];

    protected $header = '';

    protected $subHeader = '';

    protected $position = 3;

    protected $percentage = 0;

    /**
     * BarChart constructor.
     *
     * @param FeedbackCollection $feedbackCollection
     */
    public function __construct(FeedbackCollection $feedbackCollection)
    {
        $totalResponses   = 0;
        $positive = 0;
        $negative = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if($feedback->getLearnSomethingNew()) {
                $positive++;
            } else {
                $negative++;
            }

            $totalResponses++;
        }

        $this->header = 'Of Respondents Found They Learned Something New.';

        if ($totalResponses !== 0) {
            $this->percentage = round($positive / $totalResponses * 100);
        }

        $this->subHeader = sprintf("(%s Responses)", $totalResponses);

        $this->data = [$positive, $negative];
    }

    public function render()
    {
        return json_encode([
            'type' => $this->type,
            'data' => [
                'labels' => $this->labels,
                'datasets' => [
                    [
                        'data' => $this->data,
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgb(255, 99, 132)',
                        ],
                        'hoverOffset' => 4,
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
        return '';
    }

    public function getTemplate()
    {
        return 'report/dashboard/pie_chart.html.twig';
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

    /**
     * @return float
     */
    public function getPercentage(): float
    {
        return $this->percentage;
    }
}