<?php

namespace App\Model\Report\Dashboard\Feedback\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;

class StudentInterestInWorkingForCompany extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = ['1', '2', '3', '4', '5'];

    protected $label = 'Bar Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = '';

    protected $subHeader = '';

    protected $footer = 'Much Less <-> Much More';

    protected $position = 1;

    /**
     * BarChart constructor.
     *
     * @param FeedbackCollection $feedbackCollection
     */
    public function __construct(FeedbackCollection $feedbackCollection)
    {
        $data           = [];
        $totalResponses = 0;
        $totalInterest  = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if ($feedback->getFeedbackProvider() !== 'Student') {
                continue;
            }

            if ($feedback->getExperienceProvider() !== 'Company') {
                continue;
            }

            $totalResponses++;

            $interest = $feedback->getInterestWorkingForCompany();

            if ($interest == 4 || $interest == 5) {
                $totalInterest++;
            }

            if (!isset($data[$interest])) {
                $data[$interest] = 0;
            }

            $data[$interest]++;
        }

        foreach ($this->labels as $label) {
            $this->data[] = isset($data[$label]) ? $data[$label] : 0;
        }

        if ($totalResponses !== 0) {
            //$this->header = sprintf("%s%% of Students Expressed Interest in Working for the Company", round($totalInterest / $totalResponses * 100));
        }

        $this->header = 'After this company experience my awareness of career opportunities at this company is: 5 point scale from much less to much more';

        $this->subHeader = sprintf("(%s Responses)", $totalResponses);

    }

    public function render()
    {
        return json_encode([
            'type' => $this->type,
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